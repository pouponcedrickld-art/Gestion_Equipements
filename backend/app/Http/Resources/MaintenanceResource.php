<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaintenanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'panne_id' => $this->panne_id,
            'equipement_id' => $this->equipement_id,
            'technicien_id' => $this->technicien_id,
            'type_maintenance' => $this->type_maintenance,
            'date_prevue' => $this->date_prevue?->toISOString(),
            'responsable' => $this->responsable,
            'technicien' => $this->technicien,
            'diagnostic' => $this->diagnostic,
            'cout' => $this->cout,
            'date_debut' => $this->date_debut?->toISOString(),
            'date_fin' => $this->date_fin?->toISOString(),
            'observations' => $this->observations,
            'statut' => $this->statut,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // Relations
            'equipement' => $this->whenLoaded('equipement', function () {
                return $this->equipement ? [
                    'id' => $this->equipement->id,
                    'nom' => $this->equipement->nom,
                    'reference' => $this->equipement->reference,
                    'marque' => $this->equipement->marque,
                    'modele' => $this->equipement->modele,
                ] : null;
            }),
            'panne' => $this->whenLoaded('panne'),
            'technicienUser' => $this->whenLoaded('technicienUser', function () {
                return $this->technicienUser ? [
                    'id' => $this->technicienUser->id,
                    'name' => $this->technicienUser->name,
                ] : null;
            }),
        ];
    }
}
