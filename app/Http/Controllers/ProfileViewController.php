<?php

namespace App\Http\Controllers;

use App\Models\ProfileView;
use App\Http\Requests\StoreProfileViewRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProfileViewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $views = ProfileView::with(['viewer', 'viewedUser'])->paginate(20);
            return response()->json($views);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['error' => 'Non authentifié'], 401);
            }

            $request->validate([
                'viewed_id' => 'required|uuid|exists:users,id',
            ]);

            $viewedId = $request->input('viewed_id');
            
            // Ne pas permettre de voir son propre profil
            if ($user->id === $viewedId) {
                return response()->json(['error' => 'Vous ne pouvez pas voir votre propre profil'], 400);
            }

            // Vérifier si une vue existe déjà aujourd'hui pour éviter les doublons
            $existingView = ProfileView::where('viewer_id', $user->id)
                ->where('viewed_id', $viewedId)
                ->whereDate('viewed_at', now()->toDateString())
                ->first();

            if ($existingView) {
                // Mettre à jour la date de vue
                $existingView->update(['viewed_at' => now()]);
                return response()->json(['success' => true, 'profile_view' => $existingView->load(['viewer', 'viewedUser'])], 200);
            }

            $view = ProfileView::create([
                'id' => Str::uuid(),
                'viewer_id' => $user->id,
                'viewed_id' => $viewedId,
                'viewed_at' => now(),
            ]);

            return response()->json(['success' => true, 'profile_view' => $view->load(['viewer', 'viewedUser'])], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $view = ProfileView::with(['viewer', 'viewedUser'])->find($id);
            if (!$view) {
                return response()->json(['error' => 'Profile view not found'], 404);
            }
            return response()->json($view);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $view = ProfileView::find($id);
            if (!$view) {
                return response()->json(['error' => 'Profile view not found'], 404);
            }
            $data = $request->all();
            $view->update($data);
            return response()->json(['success' => true, 'profile_view' => $view->fresh(['viewer', 'viewedUser'])]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $view = ProfileView::find($id);
            if (!$view) {
                return response()->json(['error' => 'Profile view not found'], 404);
            }
            $view->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
