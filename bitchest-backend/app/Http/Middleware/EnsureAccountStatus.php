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

        // Les admins peuvent toujours accéder aux routes admin
        // Vérifier si c'est une route admin
        $path = $request->path();
        $isAdminRoute = $request->is('api/admin/*') || (strpos($path, 'api/admin/') === 0);
        
        if ($user->isAdmin() && $isAdminRoute) {
            // Les admins peuvent accéder aux routes admin même s'ils doivent changer leur mot de passe
            // sauf pour le changement de mot de passe lui-même
            if ($user->mustChangePassword() && !$isChangePasswordRoute && !$isLogoutRoute) {
                // Permettre l'accès aux routes admin même si le mot de passe doit être changé
                // mais on pourrait forcer le changement si nécessaire
                // Pour l'instant, on permet l'accès
            }
            return $next($request);
        }

        // Pour les clients, appliquer les vérifications de statut
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

