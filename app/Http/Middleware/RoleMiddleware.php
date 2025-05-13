<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $roles)
    {
        $user = Auth::user();

        // If the user is not authenticated
        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            return redirect()->route('login'); // Or a custom unauthorized page
        }

        // Exploding the roles into an array
        $rolesArray = explode('|', $roles);

        foreach ($rolesArray as $role) {
            if ($user->hasRole($role)) {
                return $next($request); // If the user has at least one of the roles, proceed
            }
        }

        // If the user doesn't have any of the roles
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Forbidden - insufficient permissions'], 403);
        }

        abort(403, 'Forbidden - You do not have permission to access this page.');
    }

}
