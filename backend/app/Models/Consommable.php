<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consommable extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'type',
        'equipement_id',
        'quantite',
    ];

    protected $casts = [
        'quantite' => 'integer',
    ];

    // ===== RELATIONS =====

    /**
     * Relation : Consommable appartient à un équipement
     */
    public function equipement()
    {
        return $this->belongsTo(Equipement::class);
    }

    // ===== SCOPES =====

    /**
     * Scope : Filtrer par type de consommable
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope : Consommables en stock faible
     */
    public function scopeStockFaible($query, $seuil = 1)
    {
        return $query->where('quantite', '<=', $seuil);
    }

    /**
     * Scope : Recherche par nom
     */
    public function scopeSearch($query, $term)
    {
        return $query->where('nom', 'like', '%' . $term . '%');
    }

    /**
     * Scope : Consommables pour un équipement donné
     */
    public function scopeForEquipement($query, $equipementId)
    {
        return $query->where('equipement_id', $equipementId);
    }

    // ===== MÉTHODES UTILITAIRES =====

    /**
     * Augmenter la quantité en stock
     */
    public function ajouterStock($quantite, $description = null)
    {
        $this->increment('quantite', $quantite);
        
        // TODO: Créer un historique des mouvements de stock si nécessaire
        
        return $this;
    }

    /**
     * Diminuer la quantité en stock
     */
    public function retirerStock($quantite, $description = null)
    {
        if ($this->quantite < $quantite) {
            throw new \Exception("Stock insuffisant. Stock actuel: {$this->quantite}, demandé: {$quantite}");
        }
        
        $this->decrement('quantite', $quantite);
        
        return $this;
    }

    /**
     * Vérifier si le stock est faible
     */
    public function isStockFaible($seuil = 1)
    {
        return $this->quantite <= $seuil;
    }

    /**
     * Obtenir le statut du stock
     */
    public function getStatutStockAttribute()
    {
        if ($this->quantite == 0) return 'rupture';
        if ($this->quantite <= 1) return 'stock_faible';
        if ($this->quantite <= 3) return 'stock_moyen';
        return 'stock_bon';
    }

    /**
     * Types de consommables disponibles
     */
    public static function getTypesDisponibles()
    {
        return [
            'batterie' => 'Batterie',
            'chargeur' => 'Chargeur',
            'cable' => 'Câble',
            'protection' => 'Protection',
            'accessoire' => 'Accessoire',
            'consommable' => 'Consommable',
        ];
    }
}