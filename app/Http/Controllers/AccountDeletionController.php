<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class AccountDeletionController extends Controller
{
    /**
     * Affiche le formulaire de demande de suppression de compte
     */
    public function showForm()
    {
        return view('account-deletion');
    }

    /**
     * Traite la demande de suppression de compte
     */
    public function requestDeletion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'reason' => 'nullable|string|max:1000',
            'confirmation' => 'required|accepted',
        ], [
            'email.required' => 'L\'adresse email est requise.',
            'email.email' => 'Veuillez fournir une adresse email valide.',
            'confirmation.required' => 'Vous devez confirmer votre demande de suppression.',
            'confirmation.accepted' => 'Vous devez confirmer votre demande de suppression.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $email = $request->input('email');
        $reason = $request->input('reason', 'Aucune raison fournie');

        // Vérifier si l'utilisateur existe
        $user = User::where('email', $email)->first();

        if (!$user) {
            return back()
                ->with('error', 'Aucun compte trouvé avec cette adresse email. Veuillez vérifier votre email ou contacter le support.')
                ->withInput();
        }

        try {
            // Envoyer un email de confirmation au support
            $this->sendDeletionRequestEmail($user, $reason);

            // Optionnel : marquer le compte pour suppression (soft delete ou flag)
            // Vous pouvez ajouter un champ 'deletion_requested_at' dans la table users

            return redirect()->route('account-deletion.success');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Une erreur est survenue lors du traitement de votre demande. Veuillez nous contacter directement par email.')
                ->withInput();
        }
    }

    /**
     * Affiche la page de confirmation de demande
     */
    public function success()
    {
        return view('account-deletion-success');
    }

    /**
     * Supprime le compte de l'utilisateur connecté (depuis l'application mobile)
     */
    public function deleteMyAccount(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json(['error' => 'Utilisateur non authentifié'], 401);
            }

            DB::transaction(function () use ($user) {
                // Supprimer les données associées
                // Couple requests
                $user->coupleRequestsSent()->delete();
                $user->coupleRequestsReceived()->delete();
                
                // Profile views
                $user->profileViews()->delete();
                
                // Notifications
                $user->notifications()->delete();
                
                // Search history
                \App\Models\SearchHistory::where('user_id', $user->id)->delete();
                
                // Si l'utilisateur est dans un couple, gérer la rupture
                if ($user->couple_id) {
                    $couple = \App\Models\Couple::find($user->couple_id);
                    if ($couple) {
                        // Mettre à jour l'autre utilisateur du couple
                        $partner = User::where('couple_id', $couple->id)
                            ->where('id', '!=', $user->id)
                            ->first();
                        
                        if ($partner) {
                            $partner->update([
                                'couple_id' => null,
                                'status' => 'SINGLE',
                            ]);
                        }
                        
                        // Supprimer le couple
                        $couple->delete();
                    }
                }
                
                // Supprimer l'avatar si existant
                if ($user->avatar_url) {
                    try {
                        $path = str_replace('/storage/', '', $user->avatar_url);
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
                    } catch (\Exception $e) {
                        // Ignorer les erreurs de suppression de fichier
                    }
                }
                
                // Révoquer tous les tokens de l'utilisateur
                $user->tokens()->delete();
                
                // Supprimer l'utilisateur
                $user->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Votre compte et toutes vos données ont été supprimés avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la suppression',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprime effectivement le compte (appelé par l'admin après vérification)
     */
    public function deleteAccount(Request $request, string $userId)
    {
        // Cette méthode devrait être protégée par un middleware admin
        // Pour l'instant, elle est accessible mais devrait être sécurisée en production

        try {
            $user = User::find($userId);
            
            if (!$user) {
                return response()->json(['error' => 'Utilisateur non trouvé'], 404);
            }

            DB::transaction(function () use ($user) {
                // Supprimer les données associées
                // Couple requests
                $user->coupleRequestsSent()->delete();
                $user->coupleRequestsReceived()->delete();
                
                // Profile views
                $user->profileViews()->delete();
                
                // Notifications
                $user->notifications()->delete();
                
                // Search history
                \App\Models\SearchHistory::where('user_id', $user->id)->delete();
                
                // Si l'utilisateur est dans un couple, gérer la rupture
                if ($user->couple_id) {
                    $couple = \App\Models\Couple::find($user->couple_id);
                    if ($couple) {
                        // Mettre à jour l'autre utilisateur du couple
                        $partner = User::where('couple_id', $couple->id)
                            ->where('id', '!=', $user->id)
                            ->first();
                        
                        if ($partner) {
                            $partner->update([
                                'couple_id' => null,
                                'status' => 'SINGLE',
                            ]);
                        }
                        
                        // Supprimer le couple
                        $couple->delete();
                    }
                }
                
                // Supprimer l'avatar si existant
                if ($user->avatar_url) {
                    try {
                        $path = str_replace('/storage/', '', $user->avatar_url);
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
                    } catch (\Exception $e) {
                        // Ignorer les erreurs de suppression de fichier
                    }
                }
                
                // Supprimer l'utilisateur
                $user->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Compte et données associées supprimés avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la suppression',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Envoie un email au support avec la demande de suppression
     */
    private function sendDeletionRequestEmail(User $user, string $reason)
    {
        // Si vous avez configuré Mail dans Laravel, décommentez ce code
        /*
        Mail::send('emails.account-deletion-request', [
            'user' => $user,
            'reason' => $reason,
        ], function ($message) use ($user) {
            $message->to('sangolgalanga@gmail.com')
                    ->subject('Demande de suppression de compte - ' . $user->email);
        });
        */
        
        // Pour l'instant, on log juste l'information
        \Log::info('Demande de suppression de compte', [
            'user_id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'reason' => $reason,
            'requested_at' => now(),
        ]);
    }
}

