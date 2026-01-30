<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StatsController extends Controller
{
    /**
     * Statistiques de l'utilisateur connecté
     */
    public function userStats(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['error' => 'Utilisateur non authentifié'], 401);
            }
            
            // Calculer les stats avec gestion d'erreurs pour chaque relation
            $stats = [
                'profile_views' => 0,
                'couple_requests_sent' => 0,
                'couple_requests_received' => 0,
                'notifications_unread' => 0,
            ];
            
            try {
                $stats['profile_views'] = $user->profileViews()->count();
            } catch (\Exception $e) {
                // Ignorer l'erreur et garder 0
            }
            
            try {
                $stats['couple_requests_sent'] = $user->coupleRequestsSent()->count();
            } catch (\Exception $e) {
                // Ignorer l'erreur et garder 0
            }
            
            try {
                $stats['couple_requests_received'] = $user->coupleRequestsReceived()->count();
            } catch (\Exception $e) {
                // Ignorer l'erreur et garder 0
            }
            
            try {
                $stats['notifications_unread'] = $user->notifications()->where('read', false)->count();
            } catch (\Exception $e) {
                // Ignorer l'erreur et garder 0
            }
            
            return response()->json(['stats' => $stats]);
        } catch (\Throwable $e) {
            // En cas d'erreur globale, retourner des stats à zéro
            return response()->json([
                'stats' => [
                    'profile_views' => 0,
                    'couple_requests_sent' => 0,
                    'couple_requests_received' => 0,
                    'notifications_unread' => 0,
                ],
            ]);
        }
    }

    /**
     * Statistiques globales de la plateforme
     */
    public function globalStats(Request $request)
    {
        try {
            $stats = [
                'users_count' => \App\Models\User::count(),
                'couples_count' => \App\Models\Couple::where('is_active', true)->count(), // Compte seulement les couples actifs
                'separated_couples_count' => \App\Models\Couple::where('is_active', false)->count(), // Compte les couples séparés
                'couple_requests_count' => \App\Models\CoupleRequest::count(),
                'profile_views_count' => \App\Models\ProfileView::count(),
            ];
            return response()->json(['stats' => $stats]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Erreur lors du calcul des stats globales',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Liste des utilisateurs ayant vu le profil de l'utilisateur connecté
     */
    public function profileViewers(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['error' => 'Utilisateur non authentifié'], 401);
            }
            
            $viewers = \App\Models\ProfileView::where('viewed_id', $user->id)
                ->with('viewer')
                ->orderBy('viewed_at', 'desc')
                ->get()
                ->pluck('viewer')
                ->filter() // Filtrer les valeurs null
                ->values(); // Réindexer le tableau
            
            return response()->json(['viewers' => $viewers]);
        } catch (\Throwable $e) {
            // En cas d'erreur, retourner une liste vide
            return response()->json(['viewers' => []]);
        }
    }
}
