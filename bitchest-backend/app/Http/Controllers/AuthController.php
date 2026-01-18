<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Services\UniversalMailService;
use App\Mail\TemporaryPasswordMailable;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|confirmed',
            'email_confirmation' => 'required|email'
        ]);

        $tempPassword = (string) random_int(10000000, 99999999); // 8 digits
        $fullName = trim($data['first_name'].' '.$data['last_name']);

        $user = User::create([
            'name' => $fullName,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => Hash::make($tempPassword),
            'role' => 'client',
            'status' => User::STATUS_PENDING,
            'must_change_password' => true,
            'euro_balance' => 0
        ]);

        // Envoyer le mot de passe temporaire généré avec service universel (fonctionne avec tous les fournisseurs)
        $mailService = app(\App\Services\UniversalMailService::class);
        $mailService->send(new TemporaryPasswordMailable($tempPassword, $user->name), $user->email);

        return response()->json([
            'message' => 'Account created. A temporary password has been sent to your email. Please change it and wait for admin validation.',
            'status' => User::STATUS_PENDING,
            'must_change_password' => true,
            'temporary_password_sent' => true,
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Email ou mot de passe invalide',
                'error' => 'Email ou mot de passe invalide'
            ], 401);
        }

        /** @var User $user */
        $user = Auth::user();

        if ($user->role === 'client') {
            if ($user->mustChangePassword()) {
                $token = $user->createToken('auth-token')->plainTextToken;
                return response()->json([
                    'user' => $user,
                    'token' => $token,
                    'must_change_password' => true,
                    'message' => 'Password change required'
                ]);
            }

            if ($user->isPendingValidation()) {
                Auth::logout();
                return response()->json([
                    'message' => 'Account awaiting admin validation'
                ], 403);
            }

            if ($user->isBlocked()) {
                Auth::logout();
                return response()->json([
                    'message' => 'Account blocked'
                ], 403);
            }

            if (!$user->isActive()) {
                Auth::logout();
                return response()->json([
                    'message' => 'Account not active'
                ], 403);
            }
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'must_change_password' => false,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    public function changePassword(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        if (!Hash::check($data['current_password'], $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect'
            ], 400);
        }

        $user->password = Hash::make($data['password']);
        $user->must_change_password = false;

        // Si l'utilisateur était en PENDING, vérifier s'il a été créé par admin
        // Les utilisateurs créés par admin ont euro_balance > 0 (généralement 500)
        // Les utilisateurs auto-enregistrés ont euro_balance = 0
        if ($user->isPending()) {
            // Si l'utilisateur a un solde initial (créé par admin), l'activer automatiquement
            if ($user->euro_balance > 0) {
                $user->status = User::STATUS_ACTIVE;
                $user->email_verified_at = now();
            } else {
                // Utilisateur auto-enregistré : attendre validation admin
                $user->status = User::STATUS_PENDING_VALIDATION;
            }
        }

        $user->save();

        $message = $user->isActive() 
            ? 'Password updated. Your account has been activated.' 
            : 'Password updated. Account awaiting admin validation.';

        return response()->json([
            'message' => $message,
            'status' => $user->status,
        ]);
    }
}
