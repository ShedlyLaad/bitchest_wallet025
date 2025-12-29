<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class EnsureAccountStatus
{
    public function handle(Request $request, Closure $next)
    {
        /** @var User|null $user */
        $user = $request->user();
        $routeName = $request->route()?->getName();

        $isChangePasswordRoute = $routeName === 'auth.change-password';
        $isLogoutRoute = $routeName === 'auth.logout';

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($user->mustChangePassword()) {
            if (!$isChangePasswordRoute && !$isLogoutRoute) {
                return response()->json(['message' => 'Password change required'], 403);
            }
            return $next($request);
        }

        if ($user->isPending()) {
            return response()->json(['message' => 'Complete the password change to continue'], 403);
        }

        if ($user->isPendingValidation() && !$isChangePasswordRoute && !$isLogoutRoute) {
            return response()->json(['message' => 'Account awaiting admin validation'], 403);
        }

        if ($user->isBlocked()) {
            return response()->json(['message' => 'Account blocked'], 403);
        }

        return $next($request);
    }
}

