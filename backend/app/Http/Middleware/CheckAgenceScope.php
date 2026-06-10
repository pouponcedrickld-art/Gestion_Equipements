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

        // Super Admin, Gestionnaire Général et Technicien Maintenance = accès total ou global
        if ($user->hasRole(['super_admin', 'gestionnaire_stock_general', 'technicien_maintenance'])) {
            return $next($request);
        }

        // Autres rôles (Chef d'agence, Gestionnaire local, etc.) : vérifier qu'ils ont un agence_id
        if (!$user->agence_id) {
            return response()->json([
                'message' => 'Votre compte n\'est rattaché à aucune agence. Veuillez contacter l\'administrateur.'
            ], 403);
        }

        // Ajouter l'agence_id à la requête pour les contrôleurs
        $request->attributes->set('user_agence_id', $user->agence_id);

        return $next($request);
    }
}
