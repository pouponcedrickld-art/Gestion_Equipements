<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les identifiants sont incorrects.'],
            ]);
        }

        // Vérifier si 2FA est activé
        if ($user->two_factor_secret) {
            return response()->json([
                'requires_2fa' => true,
                'user_id' => $user->id,
                'message' => '2FA requis'
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->formatUser($user),
        ]);
    }

    public function verify2FA(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'code' => 'required|string|size:6',
        ]);

        $user = User::findOrFail($request->user_id);

        // TODO: Vérifier le code 2FA (Google2FA ou TOTP)
        // Pour l'instant, on accepte n'importe quel code à 6 chiffres en dev
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->formatUser($user),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie'
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $this->formatUser($request->user())
        ]);
    }

    public function refresh(Request $request)
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->formatUser($user),
        ]);
    }

    public function dashboard(Request $request)
    {
        $user = $request->user();
        $role = $user->getRoleNames()->first();

        $stats = [
            'user' => $this->formatUser($user),
            'role' => $role,
            'stats' => [
                'total_equipements' => \App\Models\Equipement::count(),
                'en_stock' => \App\Models\Equipement::where('statut_global', 'en_stock_general')->count(),
                'affectes' => \App\Models\Equipement::where('statut_global', 'affecte')->count(),
                'en_panne' => \App\Models\Equipement::where('statut_global', 'en_panne')->count(),
                'transferts_en_cours' => \App\Models\Transfert::where('statut', 'expedie')->count(),
                'demandes_en_attente' => \App\Models\DemandeMateriel::where('statut', 'en_attente')->count(),
            ]
        ];

        // Scoper les stats selon le rôle
        if ($user->hasRole('gestionnaire_stock')) {
            $stats['stats'] = [
                'total_equipements' => \App\Models\Equipement::where('agence_actuelle_id', $user->agence_id)->count(),
                'en_stock_local' => \App\Models\Equipement::where('agence_actuelle_id', $user->agence_id)->where('statut_global', 'en_stock_local')->count(),
                'affectes' => \App\Models\Equipement::where('agence_actuelle_id', $user->agence_id)->where('statut_global', 'affecte')->count(),
                'en_panne' => \App\Models\Equipement::where('agence_actuelle_id', $user->agence_id)->where('statut_global', 'en_panne')->count(),
                'transferts_recus' => \App\Models\Transfert::where('agence_destination_id', $user->agence_id)->where('statut', 'expedie')->count(),
                'pannes_a_traiter' => \App\Models\Panne::whereHas('equipement', fn($q) => $q->where('agence_actuelle_id', $user->agence_id))->where('statut', 'declaree')->count(),
            ];
        }

        return response()->json($stats);
    }

    private function formatUser(User $user)
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->getRoleNames()->first(),
            'agence_id' => $user->agence_id,
            'agence' => $user->agence ? [
                'id' => $user->agence->id,
                'nom' => $user->agence->nom,
                'type' => $user->agence->type,
            ] : null,
            'permissions' => $user->getPermissionNames()->toArray(),
        ];
    }
}
