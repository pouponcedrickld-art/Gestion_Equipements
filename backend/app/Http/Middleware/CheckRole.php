<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['message' => 'Non authentifié'], 401);
        }

        $roleArray = explode('|', $roles);
        
        if (!$user->hasAnyRole($roleArray, 'api')) {
            return response()->json([
                'message' => 'Accès interdit. Rôles requis : ' . implode(', ', $roleArray)
            ], 403);
        }

        return $next($request);
    }
}
