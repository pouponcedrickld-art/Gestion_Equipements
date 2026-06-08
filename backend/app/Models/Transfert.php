<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transfert extends Model
{
    use HasFactory;

    protected $fillable = [
        'equipement_id',
        'agence_source_id',
        'agence_destination_id',
        'type_transfert',
        'demande_par_id',
        'valide_par_id',
        'date_demande',
        'date_expedition',
        'date_reception',
        'statut',
        'quantite',
        'observations',
    ];

    protected $casts = [
        'date_demande' => 'datetime',
        'date_expedition' => 'datetime',
        'date_reception' => 'datetime',
        'quantite' => 'integer',
    ];

    // ===== RELATIONS =====

    /**
     * Relation : Transfert concerne un équipement
     */
    public function equipement()
    {
        return $this->belongsTo(Equipement::class);
    }

    /**
     * Relation : Agence source du transfert
     */
    public function agenceSource()
    {
        return $this->belongsTo(Agence::class, 'agence_source_id');
    }

    /**
     * Relation : Agence destination du transfert
     */
    public function agenceDestination()
    {
        return $this->belongsTo(Agence::class, 'agence_destination_id');
    }

    /**
     * Relation : Utilisateur qui a demandé le transfert
     */
    public function demandePar()
    {
        return $this->belongsTo(User::class, 'demande_par_id');
    }

    /**
     * Relation : Utilisateur qui a validé le transfert
     */
    public function validePar()
    {
        return $this->belongsTo(User::class, 'valide_par_id');
    }

    // ===== SCOPES =====

    /**
     * Scope : Filtrer par statut
     */
    public function scopeByStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    /**
     * Scope : Filtrer par type de transfert
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type_transfert', $type);
    }

    /**
     * Scope : Transferts impliquant une agence (source ou destination)
     */
    public function scopeForAgence($query, $agenceId)
    {
        return $query->where('agence_source_id', $agenceId)
                    ->orWhere('agence_destination_id', $agenceId);
    }

    /**
     * Scope : Transferts entrants pour une agence
     */
    public function scopeEntrants($query, $agenceId)
    {
        return $query->where('agence_destination_id', $agenceId);
    }

    /**
     * Scope : Transferts sortants d'une agence
     */
    public function scopeSortants($query, $agenceId)
    {
        return $query->where('agence_source_id', $agenceId);
    }

    /**
     * Scope : Transferts en attente d'approbation
     */
    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'demande');
    }

    /**
     * Scope : Transferts expédiés (à recevoir)
     */
    public function scopeARecevoir($query)
    {
        return $query->where('statut', 'expedie');
    }

    /**
     * Scope : Transferts terminés
     */
    public function scopeTermines($query)
    {
        return $query->whereIn('statut', ['recu', 'refuse']);
    }

    // ===== MÉTHODES UTILITAIRES =====

    /**
     * Approuver le transfert
     */
    public function approuver($userId)
    {
        if ($this->statut !== 'demande') {
            throw new \Exception('Ce transfert ne peut plus être approuvé');
        }

        $this->update([
            'statut' => 'approuve',
            'valide_par_id' => $userId,
        ]);

        return $this;
    }

    /**
     * Refuser le transfert
     */
    public function refuser($userId, $observations = null)
    {
        if ($this->statut !== 'demande') {
            throw new \Exception('Ce transfert ne peut plus être refusé');
        }

        $this->update([
            'statut' => 'refuse',
            'valide_par_id' => $userId,
            'observations' => $observations ?: $this->observations,
        ]);

        return $this;
    }

    /**
     * Expédier le transfert
     */
    public function expedier($userId)
    {
        if ($this->statut !== 'approuve') {
            throw new \Exception('Ce transfert doit être approuvé avant expédition');
        }

        $this->update([
            'statut' => 'expedie',
            'date_expedition' => now(),
        ]);

        // Changer le statut de l'équipement
        $this->equipement->changerStatut('en_transit', $userId, "Expédition vers {$this->agenceDestination->nom}");

        return $this;
    }

    /**
     * Recevoir le transfert
     */
    public function recevoir($userId)
    {
        if ($this->statut !== 'expedie') {
            throw new \Exception('Ce transfert n\'a pas été expédié');
        }

        $this->update([
            'statut' => 'recu',
            'date_reception' => now(),
        ]);

        // Mettre à jour l'équipement
        $this->equipement->changerAgence(
            $this->agence_destination_id,
            $userId,
            "Réception du transfert ID: {$this->id}"
        );

        // Changer le statut selon le type d'agence de destination
        $nouveauStatut = $this->agenceDestination->type === 'generale' ? 'en_stock_general' : 'en_stock_local';
        $this->equipement->changerStatut($nouveauStatut, $userId, "Réception en stock");

        return $this;
    }

    /**
     * Obtenir les statuts disponibles
     */
    public static function getStatusDisponibles()
    {
        return [
            'demande' => 'En demande',
            'approuve' => 'Approuvé',
            'expedie' => 'Expédié',
            'recu' => 'Reçu',
            'refuse' => 'Refusé',
        ];
    }

    /**
     * Obtenir les types de transfert disponibles
     */
    public static function getTypesDisponibles()
    {
        return [
            'livraison_generale' => 'Livraison générale',
            'retour_generale' => 'Retour général',
            'transfert_interne' => 'Transfert interne',
        ];
    }

    /**
     * Vérifier si le transfert peut être modifié
     */
    public function canBeModified()
    {
        return in_array($this->statut, ['demande']);
    }

    /**
     * Vérifier si le transfert peut être annulé
     */
    public function canBeCancelled()
    {
        return in_array($this->statut, ['demande', 'approuve']);
    }

    /**
     * Obtenir la durée du transfert
     */
    public function getDureeTransfertAttribute()
    {
        if (!$this->date_expedition || !$this->date_reception) {
            return null;
        }

        return $this->date_expedition->diffInDays($this->date_reception);
    }
}