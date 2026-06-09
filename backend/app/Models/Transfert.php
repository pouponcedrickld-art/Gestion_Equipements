<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transfert extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'equipement_id',
        'agence_source_id',
        'agence_destination_id',
        'type_transfert', // livraison_generale, retour_generale, transfert_interne
        'statut', // brouillon, en_attente_expedition, en_transit, recu, annule
        'date_demande',
        'date_expedition',
        'date_reception',
        'demande_par_id',
        'valide_par_id',
        'quantite',
        'observations',
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
            'statut' => 'en_attente_expedition',
            'valide_par_id' => $userId
        ]);
    }

    public function refuser($userId, $observations = null)
    {
        $this->update([
            'statut' => 'annule',
            'valide_par_id' => $userId,
            'observations' => $this->observations . "\nRefusé par admin: " . $observations
        ]);
    }

    public function expedier($userId)
    {
        $this->update([
            'statut' => 'en_transit',
            'date_expedition' => now()
        ]);
    }

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
                'transfert_recu',
                "Réception transfert depuis " . $this->agenceSource->nom,
                $userId
            );
        }
    }

    public static function getStatusDisponibles()
    {
        return [
            'brouillon' => 'Brouillon',
            'en_attente_expedition' => 'En attente d\'expédition',
            'en_transit' => 'En transit',
            'recu' => 'Reçu',
            'annule' => 'Annulé'
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
