<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\StockAgenceService;
use App\Events\TransfertCree;
use App\Events\TransfertValide;
use Illuminate\Support\Facades\App;

class Transfert extends Model
{
    

    protected $fillable = [
        'demande_materiel_id',
        'equipement_id',
        'agence_source_id',
        'agence_destination_id',
        'type_transfert', // livraison_generale, retour_generale, transfert_interne
        'statut', // demande, approuve, expedie, recu, refuse
        'date_demande',
        'date_expedition',
        'date_reception',
        'demande_par_id',
        'valide_par_id',
        'quantite',
        'observations',
        'motif_refus',
    ];

    protected $casts = [
        'date_demande' => 'datetime',
        'date_expedition' => 'datetime',
        'date_reception' => 'datetime',
    ];

    /**
     * Scopes
     */
    public function scopeByStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    public function scopeForAgence($query, $agenceId)
    {
        return $query->where(function($q) use ($agenceId) {
            $q->where('agence_source_id', $agenceId)
              ->orWhere('agence_destination_id', $agenceId);
        });
    }

    public function scopeEntrants($query, $agenceId)
    {
        return $query->where('agence_destination_id', $agenceId);
    }

    public function scopeSortants($query, $agenceId)
    {
        return $query->where('agence_source_id', $agenceId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type_transfert', $type);
    }

    /**
     * Relations
     */
    public function demandeMateriel()
    {
        return $this->belongsTo(DemandeMateriel::class, 'demande_materiel_id');
    }

    public function equipement()
    {
        return $this->belongsTo(Equipement::class);
    }

    public function agenceSource()
    {
        return $this->belongsTo(Agence::class, 'agence_source_id');
    }

    public function agenceDestination()
    {
        return $this->belongsTo(Agence::class, 'agence_destination_id');
    }

    public function demandePar()
    {
        return $this->belongsTo(User::class, 'demande_par_id');
    }

    public function validePar()
    {
        return $this->belongsTo(User::class, 'valide_par_id');
    }

    /**
     * Méthodes de workflow
     */
    public function approuver($userId)
    {
        $this->update([
            'statut' => 'approuve',
            'valide_par_id' => $userId
        ]);

        event(new TransfertValide($this->equipement, $this->agenceDestination));
    }

    public function refuser($userId, $observations = null)
    {
        $oldStatus = $this->statut;

        $this->update([
            'statut' => 'refuse',
            'valide_par_id' => $userId,
            'motif_refus' => $observations,
            'observations' => ($this->observations ?? '') . "\nRefusé/Annulé : " . ($observations ?? '')
        ]);

        // Ajuster le stock selon l'ancien statut
        if ($oldStatus === 'expedie') {
            // Stock retiré de la source lors de l'expédition → on le remet à la source
            $stockService = App::make(StockAgenceService::class);
            $stockService->incrementerStock($this->agence_source_id, $this);
        } elseif ($oldStatus === 'recu') {
            // Stock ajouté à la destination lors de la réception → on l'enlève
            $stockService = App::make(StockAgenceService::class);
            $stockService->decrementerStock($this, 'rejet');
        }
    }
// --- Expédition du transfert --- 
    public function expedier($userId)
    {
        $this->update([
            'statut' => 'expedie',
            'date_expedition' => now()
        ]);

        // Décrementer le stock de l'agence source
        $stockService = App::make(StockAgenceService::class);
        $stockService->decrementerStock($this, 'expedition');

        // Mettre à jour le statut de l'équipement
        if ($this->equipement) {
            $this->equipement->update([
                'statut_global' => 'en_transit',
                'localisation' => 'En transfert vers ' . ($this->agenceDestination->nom ?? 'Destination')
            ]);

            $this->equipement->createMouvement(
                'transfert',
                "Expédition du transfert vers " . ($this->agenceDestination->nom ?? 'Destination'),
                $userId
            );
        }
    }
// --- Réception du transfert --- 
    public function recevoir($userId)
    {
        $this->update([
            'statut' => 'recu',
            'date_reception' => now()
        ]);

        // Mettre à jour la localisation de l'équipement
        if ($this->equipement) {
            $this->equipement->update([
                'agence_actuelle_id' => $this->agence_destination_id,
                'statut_global' => $this->type_transfert === 'retour_generale' ? 'en_stock_general' : 'en_service'
            ]);
            
            $this->equipement->createMouvement(
                'transfert',
                "Réception transfert depuis " . ($this->agenceSource->nom ?? 'Origine'),
                $userId
            );
        }

        // Incrémenter le stock de l'agence destinataire
        $stockService = App::make(StockAgenceService::class);
        $stockService->incrementerStockReception($this);
    }

    public static function getStatusDisponibles()
    {
        return [
            'demande' => 'En attente de validation',
            'approuve' => 'Approuvé / Prêt à expédier',
            'expedie' => 'En transit',
            'recu' => 'Reçu',
            'refuse' => 'Refusé'
        ];
    }

    public static function getTypesDisponibles()
    {
        return [
            'livraison_generale' => 'Livraison Siège -> Agence',
            'retour_generale' => 'Retour Agence -> Siège',
            'transfert_interne' => 'Transfert Inter-Agence'
        ];
    }
}
