<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PanneResource extends JsonResource
{
    /**
     * Transforme une panne en format API.
     */
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,
            'equipement_id' => $this->equipement_id,
            'agent_id' => $this->agent_id,
            'gestionnaire_stock_id' => $this->gestionnaire_stock_id,
            'technicien_id' => $this->technicien_id,
            'date_declaration' => $this->date_declaration?->toISOString(),
            'description' => $this->description,
            'niveau_gravite' => $this->niveau_gravite,
            'photos' => $this->photos,
            'statut' => $this->statut,
            'diagnostic_technicien' => $this->diagnostic_technicien,
            'action_realisee' => $this->action_realisee,
            'cout_reparation' => $this->cout_reparation,
            'date_resolution' => $this->date_resolution?->toISOString(),
            'decision_finale' => $this->decision_finale,

            // relations (optionnelles)
            'equipement' => $this->whenLoaded('equipement', function () {
                return [
                    'id' => $this->equipement->id,
                    'nom' => $this->equipement->nom,
                    'reference' => $this->equipement->reference,
                    'numero_serie' => $this->equipement->numero_serie,
                ];
            }),
            'agent' => $this->whenLoaded('agent', function () {
                return [
                    'id' => $this->agent->id,
                    'nom' => $this->agent->nom ?? null,
                    'prenom' => $this->agent->prenom ?? null,
                    'user_id' => $this->agent->user_id ?? null,
                ];
            }),

            'technicien' => $this->whenLoaded('technicien', function () {
                return $this->technicien ? [
                    'id' => $this->technicien->id,
                    'name' => $this->technicien->name ?? null,
                ] : null;
            }),
            'gestionnaireStock' => $this->whenLoaded('gestionnaireStock', function () {
                return $this->gestionnaireStock ? [
                    'id' => $this->gestionnaireStock->id,
                    'name' => $this->gestionnaireStock->name ?? null,
                ] : null;
            }),

            // historisation (optionnel)
            'statusHistories' => $this->whenLoaded('statusHistories', function () {
                return $this->statusHistories->map(function ($h) {
                    return [
                        'id' => $h->id,
                        'statut_ancien' => $h->statut_ancien,
                        'statut_nouveau' => $h->statut_nouveau,
                        'commentaire' => $h->commentaire,
                        'action_realisee' => $h->action_realisee,
                        'cout_reparation' => $h->cout_reparation,
                        'created_at' => $h->created_at?->toISOString(),
                        'created_by' => $h->created_by,
                    ];
                });
            }),
        ];
    }
}

