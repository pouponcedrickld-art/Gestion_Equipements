<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAgenceScope
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['message' => 'Non authentifié'], 401);
        }

        // Super Admin et Gestionnaire Général = accès total
        if ($user->hasRole(['super_admin', 'gestionnaire_stock_general'])) {
            return $next($request);
        }

        // Autres rôles : vérifier qu'ils ont un agence_id
        if (!$user->agence_id) {
            return response()->json([
                'message' => 'Utilisateur non assigné à une agence'
            ], 403);
        }

        // Ajouter l'agence_id à la requête pour les contrôleurs
        $request->attributes->set('user_agence_id', $user->agence_id);

        return $next($request);
    }
}
