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
            'message' => 'Compte créé. Un mot de passe temporaire a été envoyé par email. Changez-le puis attendez la validation admin.',
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

        if ($user->isPending()) {
            $user->status = User::STATUS_PENDING_VALIDATION;
        }

        $user->save();

        return response()->json([
            'message' => 'Password updated. Account awaiting admin validation.',
            'status' => $user->status,
        ]);
    }
}
