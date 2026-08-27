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

        // L'envoi de l'email ne fait jamais échouer la création du compte : le service
        // bascule sur le mailer "log" si le SMTP est indisponible (limite d'envoi, etc.).
        $mailService = app(\App\Services\UniversalMailService::class);
        $temporaryPasswordSent = $mailService->send(
            new TemporaryPasswordMailable($tempPassword, $user->name),
            $user->email
        );

        return response()->json([
            'message' => $temporaryPasswordSent
                ? 'Account created. A temporary password has been sent to your email. Please change it and wait for admin validation.'
                : 'Account created and pending admin validation. The temporary password email could not be sent — an administrator can resend it.',
            'status' => User::STATUS_PENDING,
            'must_change_password' => true,
            'temporary_password_sent' => $temporaryPasswordSent,
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid email or password',
                'error' => 'Invalid email or password'
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
        $user = $request->user();

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => [
                'required',
                'string',
                'confirmed',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            ],
        ], [
            'password.required' => 'A new password is required.',
            'password.min' => 'The new password must be at least 8 characters long.',
            'password.confirmed' => 'The two passwords do not match.',
            'password.regex' => 'The new password must contain at least 1 uppercase letter, 1 lowercase letter and 1 digit.',
        ]);

        if (!Hash::check($data['current_password'], $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect'
            ], 400);
        }

        $user->password = Hash::make($data['password']);
        $user->must_change_password = false;

        if ($user->isPending()) {
            if ($user->euro_balance > 0) {
                $user->status = User::STATUS_ACTIVE;
                $user->email_verified_at = now();
            } else {
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
