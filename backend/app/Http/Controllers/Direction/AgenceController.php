<?php
// =============================================
// FICHIER : AgenceController.php
// RÔLE : Contrôleur CRUD pour les agences (pour la direction)
// Gère la liste, la création, la modification, la suppression et les stats des agences
// =============================================

namespace App\Http\Controllers\Direction;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AgenceController extends Controller
{
    // =============================================
    // MÉTHODE : Lister toutes les agences
    // =============================================
    public function index()
    {
        // Vérifie si l'utilisateur a le droit de voir les agences
        Gate::authorize('viewAny', Agence::class);
        // Récupère toutes les agences avec leurs sous-agences, responsable et gestionnaire de stock
        return Agence::with(['sousAgences', 'responsable', 'gestionnaireStock'])->get();
    }

    // =============================================
    // MÉTHODE : Créer une nouvelle agence
    // =============================================
    public function store(Request $r)
    {
        // Vérifie si l'utilisateur a le droit de créer une agence
        Gate::authorize('create', Agence::class);
        // Valide les données du formulaire
        $r->validate([
            'type' => 'required|in:generale,sous_agence',
            'nom' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:agences,id',
            'ville' => 'nullable|string|max:100',
            'adresse' => 'nullable|string',
            'code_postal' => 'nullable|string|max:20',
            'telephone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
        ]);
        // Crée l'agence et la retourne
        return Agence::create($r->all());
    }

    // =============================================
    // MÉTHODE : Afficher une agence spécifique
    // =============================================
    public function show(Agence $agence)
    {
        // Vérifie si l'utilisateur a le droit de voir cette agence
        Gate::authorize('view', $agence);
        // Charge les relations et retourne l'agence
        return $agence->load(['sousAgences', 'responsable', 'gestionnaireStock', 'users']);
    }

    // =============================================
    // MÉTHODE : Mettre à jour une agence
    // =============================================
    public function update(Request $r, Agence $agence)
    {
        // Vérifie si l'utilisateur a le droit de modifier cette agence
        Gate::authorize('update', $agence);
        // Valide les données du formulaire
        $r->validate([
            'nom' => 'sometimes|string|max:255',
            'adresse' => 'nullable|string',
            'ville' => 'nullable|string|max:100',
            'code_postal' => 'nullable|string|max:20',
            'telephone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'responsable_id' => 'nullable|exists:users,id',
            'gestionnaire_stock_id' => 'nullable|exists:users,id',
            'statut' => 'sometimes|in:active,inactive',
        ]);
        // Met à jour l'agence
        $agence->update($r->only(['nom', 'adresse', 'ville', 'code_postal', 'telephone', 'email', 'responsable_id', 'gestionnaire_stock_id', 'statut']));
        // Retourne l'agence mise à jour avec ses relations
        return $agence->load(['responsable', 'gestionnaireStock']);
    }

    // =============================================
    // MÉTHODE : Supprimer une agence
    // =============================================
    public function destroy(Agence $agence)
    {
        // Vérifie si l'utilisateur a le droit de supprimer cette agence
        Gate::authorize('delete', $agence);
        // Vérifie si l'agence a des sous-agences (si oui, on ne peut pas la supprimer)
        if ($agence->sousAgences()->count() > 0) {
            return response()->json(['message' => 'Impossible de supprimer une agence ayant des sous-agences'], 422);
        }
        // Supprime l'agence
        $agence->delete();
        // Retourne une réponse sans contenu (204 No Content)
        return response()->noContent();
    }

    // =============================================
    // MÉTHODE : Obtenir les statistiques d'une agence
    // =============================================
    public function stats(Agence $agence)
    {
        // Vérifie si l'utilisateur a le droit de voir cette agence
        Gate::authorize('view', $agence);
        // Retourne les stats de l'agence
        return [
            'equipements_count' => $agence->equipements()->count(),
            'agents_count' => $agence->users()->whereHas('roles', fn($q) => $q->where('name', 'agent'))->count(),
            'pannes_actives' => $agence->equipements()->where('statut_global', 'en_panne')->count(),
            'transferts_en_cours' => $agence->transfertsDestination()->where('statut', 'expedie')->count(),
        ];
    }
}
