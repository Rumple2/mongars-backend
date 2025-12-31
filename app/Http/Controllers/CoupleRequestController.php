<?php

namespace App\Http\Controllers;

use App\Models\CoupleRequest;
use App\Models\Couple;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CoupleRequestController extends Controller
{
    /**
     * Récupère les demandes reçues par l'utilisateur connecté
     */
    public function received(Request $request)
    {
        try {
            $user = $request->user();
            $requests = CoupleRequest::where('receiver_id', $user->id)
                ->with(['sender', 'receiver'])
                ->orderBy('sent_at', 'desc')
                ->get();
            return response()->json(['data' => $requests]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Récupère les demandes envoyées par l'utilisateur connecté
     */
    public function sent(Request $request)
    {
        try {
            $user = $request->user();
            $requests = CoupleRequest::where('sender_id', $user->id)
                ->with(['sender', 'receiver'])
                ->orderBy('sent_at', 'desc')
                ->get();
            return response()->json(['data' => $requests]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Envoie une demande de couple
     */
    public function sendRequest(Request $request)
    {
        try {
            $user = $request->user();
            $data = $request->validate([
                'sender_id' => 'required|uuid|exists:users,id',
                'receiver_id' => 'required|uuid|exists:users,id|different:sender_id',
                'status' => 'in:PENDING,ACCEPTED,REJECTED,CANCELLED',
                'message' => 'nullable|string',
            ]);

            // Vérifier que l'utilisateur connecté est bien le sender
            if ($data['sender_id'] !== $user->id) {
                return response()->json(['error' => 'Vous ne pouvez envoyer une demande qu\'en votre nom'], 403);
            }

            // Vérifier qu'il n'y a pas déjà une demande en cours
            $existingRequest = CoupleRequest::where('sender_id', $data['sender_id'])
                ->where('receiver_id', $data['receiver_id'])
                ->whereIn('status', ['PENDING', 'ACCEPTED'])
                ->first();

            if ($existingRequest) {
                return response()->json(['error' => 'Une demande existe déjà avec cet utilisateur'], 400);
            }

            $coupleRequestData = [
                'sender_id' => $data['sender_id'],
                'receiver_id' => $data['receiver_id'],
                'status' => $data['status'] ?? 'PENDING',
            ];

            if (isset($data['message'])) {
                $coupleRequestData['message'] = $data['message'];
            }

            $coupleRequest = CoupleRequest::create($coupleRequestData);
            return response()->json(['success' => true, 'data' => $coupleRequest->load(['sender', 'receiver'])], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Répond à une demande de couple (accepter ou refuser)
     */
    public function respond(Request $request, string $id)
    {
        try {
            $user = $request->user();
            $coupleRequest = CoupleRequest::with(['sender', 'receiver'])->find($id);

            if (!$coupleRequest) {
                return response()->json(['error' => 'Demande de couple non trouvée'], 404);
            }

            // Vérifier que l'utilisateur connecté est le receiver
            if ($coupleRequest->receiver_id !== $user->id) {
                return response()->json(['error' => 'Vous n\'êtes pas autorisé à répondre à cette demande'], 403);
            }

            // Vérifier que la demande est en attente
            if ($coupleRequest->status !== 'PENDING') {
                return response()->json(['error' => 'Cette demande a déjà été traitée'], 400);
            }

            $data = $request->validate([
                'accept' => 'boolean',
                'status' => 'in:ACCEPTED,REJECTED',
            ]);

            // Utiliser 'accept' si fourni, sinon 'status'
            if (isset($data['accept'])) {
                $coupleRequest->status = $data['accept'] ? 'ACCEPTED' : 'REJECTED';
            } else {
                $coupleRequest->status = $data['status'];
            }

            $coupleRequest->responded_at = now();
            $coupleRequest->save();

            // Si accepté, créer le couple et mettre à jour les utilisateurs
            if ($coupleRequest->status === 'ACCEPTED') {
                DB::transaction(function () use ($coupleRequest) {
                    // Vérifier qu'aucun des deux utilisateurs n'est déjà en couple
                    $sender = User::find($coupleRequest->sender_id);
                    $receiver = User::find($coupleRequest->receiver_id);
                    
                    if ($sender->status === 'IN_RELATIONSHIP' || $receiver->status === 'IN_RELATIONSHIP') {
                        throw new \Exception('Un des utilisateurs est déjà en couple');
                    }

                    // Créer le couple
                    $couple = Couple::create([
                        'user1_id' => $coupleRequest->sender_id,
                        'user2_id' => $coupleRequest->receiver_id,
                        'is_active' => true,
                    ]);

                    // Mettre à jour les deux utilisateurs
                    $sender->update([
                        'status' => 'IN_RELATIONSHIP',
                        'couple_id' => $couple->id,
                    ]);

                    $receiver->update([
                        'status' => 'IN_RELATIONSHIP',
                        'couple_id' => $couple->id,
                    ]);
                });
            }

            return response()->json(['success' => true, 'data' => $coupleRequest->fresh(['sender', 'receiver'])]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
