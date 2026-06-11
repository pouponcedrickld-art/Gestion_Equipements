<?php

namespace App\Services;

use App\Models\StockAgence;
use App\Models\Transfert;
use App\Models\Equipement;
use Illuminate\Support\Facades\DB;

class StockAgenceService
{
    /**
     * Incrémenter le stock d'une agence lors de la réception d'un transfert
     */
    public function incrementerStockReception(Transfert $transfert): StockAgence
    {
        return DB::transaction(function () use ($transfert) {
            $agenceId = $transfert->agence_destination_id;
            $equipementId = $transfert->equipement_id;
            
            // Récupérer ou créer le stock pour cette agence et cet équipement
            $stock = StockAgence::firstOrCreate(
                [
                    'agence_id' => $agenceId,
                    'equipement_id' => $equipementId,
                    'categorie_id' => $transfert->equipement->categorie_id,
                ],
                [
                    'quantite' => 0,
                    'quantite_disponible' => 0,
                    'quantite_reservee' => 0,
                ]
            );

            // Déterminer la quantité à ajouter (1 pour un équipement unique, ou la quantité pour un lot)
            $quantite = $this->determinerQuantite($transfert->equipement);

            // Mettre à jour le stock
            $stock->increment('quantite', $quantite);
            $stock->increment('quantite_disponible', $quantite);
            $stock->date_derniere_mise_a_jour = now();
            $stock->save();

            return $stock;
        });
    }

    /**
     * Décrementer le stock d'une agence lors du rejet d'un transfert
     * ou lors de l'expédition d'un transfert sortant
     */
    public function decrementerStock(Transfert $transfert, string $type): ?StockAgence
    {
        return DB::transaction(function () use ($transfert, $type) {
            $agenceId = $type === 'rejet' 
                ? $transfert->agence_destination_id 
                : $transfert->agence_origine_id;
            
            $equipementId = $transfert->equipement_id;
            
            $stock = StockAgence::where('agence_id', $agenceId)
                ->where('equipement_id', $equipementId)
                ->first();

            if (!$stock) {
                return null;
            }

            $quantite = $this->determinerQuantite($transfert->equipement);

            // Mettre à jour le stock
            $stock->decrement('quantite', $quantite);
            $stock->decrement('quantite_disponible', $quantite);
            $stock->date_derniere_mise_a_jour = now();
            $stock->save();

            // Si le stock devient 0, on peut le supprimer ou le conserver (au choix)
            if ($stock->quantite <= 0) {
                // Optionnel : $stock->delete();
            }

            return $stock;
        });
    }

    /**
     * Réserver une quantité en stock pour une demande en attente
     */
    public function reserverStock(int $agenceId, int $equipementId, int $quantite = 1): ?StockAgence
    {
        return DB::transaction(function () use ($agenceId, $equipementId, $quantite) {
            $stock = StockAgence::where('agence_id', $agenceId)
                ->where('equipement_id', $equipementId)
                ->first();

            if (!$stock || $stock->quantite_disponible < $quantite) {
                return null;
            }

            $stock->increment('quantite_reservee', $quantite);
            $stock->decrement('quantite_disponible', $quantite);
            $stock->date_derniere_mise_a_jour = now();
            $stock->save();

            return $stock;
        });
    }

    /**
     * Annuler une réservation de stock
     */
    public function annulerReservation(int $agenceId, int $equipementId, int $quantite = 1): ?StockAgence
    {
        return DB::transaction(function () use ($agenceId, $equipementId, $quantite) {
            $stock = StockAgence::where('agence_id', $agenceId)
                ->where('equipement_id', $equipementId)
                ->first();

            if (!$stock) {
                return null;
            }

            $stock->decrement('quantite_reservee', $quantite);
            $stock->increment('quantite_disponible', $quantite);
            $stock->date_derniere_mise_a_jour = now();
            $stock->save();

            return $stock;
        });
    }

    /**
     * Déterminer la quantité à modifier pour un équipement
     */
    private function determinerQuantite(Equipement $equipement): int
    {
        // Si c'est un lot, utiliser la quantité du lot, sinon 1
        return $equipement->is_lot && $equipement->quantite > 0 
            ? $equipement->quantite 
            : 1;
    }
}
