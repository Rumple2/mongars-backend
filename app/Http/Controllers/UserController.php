<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage as LaravelStorage;

class UserController extends Controller
{
    /**
     * Recherche des utilisateurs par nom, email, username ou téléphone.
     */
    public function search(Request $request)
    {
        try {
            $query = $request->input('query');
            if (!$query || trim($query) === '') {
                return response()->json(['error' => 'Aucun terme de recherche fourni'], 400);
            }

            // Nettoyer la requête
            $searchTerm = trim($query);
            
            // Rechercher dans tous les champs avec des conditions OR groupées
            $users = User::where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%$searchTerm%")
                  ->orWhere('email', 'like', "%$searchTerm%")
                  ->orWhere('username', 'like', "%$searchTerm%")
                  ->orWhere('phone', 'like', "%$searchTerm%");
            })->get();

            if ($users->isEmpty()) {
                return response()->json(['error' => 'Aucun utilisateur trouvé'], 404);
            }
            
            return response()->json(['users' => $users]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Erreur lors de la recherche',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Retourne l'utilisateur connecté.
     */
    public function me(Request $request)
    {
        try {
            return response()->json($request->user());
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération de l\'utilisateur',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Met à jour le profil de l'utilisateur connecté.
     */
    public function updateMe(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['error' => 'Utilisateur non authentifié'], 401);
            }

            $data = $request->only(['name', 'username', 'email', 'phone', 'date_of_birth', 'status']);
            
            // Ne pas permettre la modification de certains champs sensibles
            unset($data['id'], $data['password'], $data['is_verified'], $data['is_premium']);
            
            $user->update($data);
            
            return response()->json(['success' => true, 'user' => $user->fresh()]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour du profil',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Met à jour l'avatar de l'utilisateur connecté.
     */
    public function uploadAvatar(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['error' => 'Utilisateur non authentifié'], 401);
            }

            $request->validate([
                'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $filename = time() . '_' . $user->id . '.' . $file->getClientOriginalExtension();
                
                // Stocker le fichier dans storage/app/public/avatars
                $path = $file->storeAs('public/avatars', $filename);
                
                // Générer l'URL publique
                $url = LaravelStorage::url('avatars/' . $filename);
                
                // Mettre à jour l'URL de l'avatar dans la base de données
                $user->avatar_url = $url;
                $user->save();
                
                return response()->json([
                    'success' => true,
                    'user' => $user->fresh(),
                ]);
            }

            return response()->json(['error' => 'Aucun fichier fourni'], 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation échouée',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Erreur lors de l\'upload de l\'avatar',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $users = User::paginate(20);
            return response()->json($users);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        try {
            $data = $request->validated();
            $data['id'] = Str::uuid();
            $data['status'] = $data['status'] ?? 'SINGLE';
            $data['is_verified'] = false;
            $data['is_premium'] = false;
            $data['password'] = bcrypt($data['password']);
            $user = User::create($data);
            return response()->json(['success' => true, 'user' => $user], 201);
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
            $user = User::find($id);
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }
            return response()->json($user);
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
            $user = User::find($id);
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }
            $data = $request->all();
            if (isset($data['password'])) {
                $data['password'] = bcrypt($data['password']);
            }
            $user->update($data);
            return response()->json(['success' => true, 'user' => $user]);
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
            $user = User::find($id);
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }
            $user->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
