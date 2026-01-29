<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;

class AuthController extends Controller
{
    public function __construct(private FirebaseAuth $firebaseAuth)
    {
    }
    public function register(Request $request)
    {
        try {

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|unique:users,email',
                'phone' => 'nullable|string|unique:users,phone',
                'password' => 'required_unless:auth_method,GOOGLE|string|min:6',
                'auth_method' => 'required|in:EMAIL,PHONE,GOOGLE',
            ]);

            $userData = [
                'id' => \Illuminate\Support\Str::uuid(),
                'name' => $validated['name'],
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'auth_method' => $validated['auth_method'],
                'is_verified' => $validated['auth_method'] === 'GOOGLE',
            ];

            if ($validated['auth_method'] === 'EMAIL') {
                $userData['password'] = bcrypt($validated['password']);
            } elseif ($validated['auth_method'] === 'PHONE') {
                $userData['password'] = null;
            } elseif ($validated['auth_method'] === 'GOOGLE') {
                $userData['password'] = null;
            }

            $user = User::create($userData);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token,
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage(),
            ], 400);
        }
    }
    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'nullable|email',
                'phone' => 'nullable|string',
                'password' => 'required|string',
            ]);

            $user = null;
            if (!empty($validated['email'])) {
                $user = \App\Models\User::where('email', $validated['email'])->first();
            } elseif (!empty($validated['phone'])) {
                $user = \App\Models\User::where('phone', $validated['phone'])->first();
            }

            if (!$user || !$user->password || !\Illuminate\Support\Facades\Hash::check($validated['password'], $user->password)) {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Login failed',
                'error' => $e->getMessage(),
            ], 400);
        }
    }
    public function sendOtp(Request $request)
    {
        try {
            $validated = $request->validate([
                'phone' => 'required|string',
            ]);

            $user = \App\Models\User::where('phone', $validated['phone'])->first();
            if (!$user) {
                $user = \App\Models\User::create([
                    'id' => \Illuminate\Support\Str::uuid(),
                    'name' => 'User_' . substr($validated['phone'], -4),
                    'phone' => $validated['phone'],
                    'auth_method' => 'PHONE',
                    'is_verified' => false,
                ]);
            }

            $otp = random_int(100000, 999999);
            \Illuminate\Support\Facades\Cache::put('otp_' . $validated['phone'], $otp, now()->addMinutes(10));

            // TODO: Send OTP via SMS provider here

            return response()->json([
                'message' => 'OTP sent',
                'otp' => env('APP_ENV') === 'local' ? $otp : null,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'OTP sending failed',
                'error' => $e->getMessage(),
            ], 400);
        }
    }
    public function verifyOtp(Request $request)
    {
        try {
            $validated = $request->validate([
                'phone' => 'required|string',
                'otp' => 'required|digits:6',
            ]);

            $cachedOtp = \Illuminate\Support\Facades\Cache::get('otp_' . $validated['phone']);
            if (!$cachedOtp || $cachedOtp != $validated['otp']) {
                return response()->json(['message' => 'Invalid or expired OTP'], 400);
            }

            $user = \App\Models\User::where('phone', $validated['phone'])->first();
            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            $user->is_verified = true;
            $user->save();
            \Illuminate\Support\Facades\Cache::forget('otp_' . $validated['phone']);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'OTP verification failed',
                'error' => $e->getMessage(),
            ], 400);
        }
    }
    public function google(Request $request)
    {
        // Le front envoie l'ID token Firebase sous la clé "id_token"
        $validated = $request->validate([
            'id_token' => 'required|string',
        ]);

        try {
            // 1) Vérifier le token auprès de Firebase
            $verifiedToken = $this->firebaseAuth->verifyIdToken($validated['id_token'], false, 60); // 60 seconds leeway
            $claims = $verifiedToken->claims();

            $uid = $claims->get('sub');
            $email = $claims->get('email');
            $name = $claims->get('name');

            if (!$email) {
                return response()->json([
                    'message' => 'Impossible de récupérer l’email depuis le token Google',
                ], 422);
            }

            // 2) Créer / récupérer l’utilisateur dans ta base
            $user = User::where('email', $email)->first();
            if (!$user) {
                $user = User::create([
                    'id'          => \Illuminate\Support\Str::uuid(),
                    'name'        => $name ?: 'Utilisateur Google',
                    'email'       => $email,
                    'auth_method' => 'GOOGLE',
                    'is_verified' => true,
                ]);
            } else {
                // S'assurer que l'utilisateur est marqué comme vérifié et auth_method à JOUR
                $user->auth_method = 'GOOGLE';
                $user->is_verified = true;
                $user->save();
            }

            // 3) Générer le token d’API (Sanctum)
            $token = $user->createToken('auth_token')->plainTextToken;

            // 4) Réponse alignée avec /auth/login et /auth/register
            return response()->json([
                'user'  => $user,
                'token' => $token,
            ]);
        } catch (FailedToVerifyToken $e) {
            return response()->json([
                'message' => 'Token Google invalide',
                'error'   => $e->getMessage(),
            ], 401);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Google login failed',
                'error'   => $e->getMessage(),
            ], 400);
        }
    }
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json(['message' => 'Logged out successfully']);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Logout failed',
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
