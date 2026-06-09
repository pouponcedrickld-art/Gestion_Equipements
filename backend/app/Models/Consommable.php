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
        'seuil_alerte',
    ];

    protected $casts = [
        'quantite' => 'integer',
        'seuil_alerte' => 'integer',
    ];

    // ===== RELATIONS =====

    /**
     * Relation : Consommable appartient à un équipement
     */
    public function equipement()
    {
        return $this->belongsTo(Equipement::class);
    }

    public function mouvements()
    {
        return $this->hasMany(Mouvement::class);
    }

    public function createMouvement($type, $description, $userId = null)
    {
        return $this->mouvements()->create([
            'type_mouvement' => $type,
            'description' => $description,
            'user_id' => $userId ?? auth()->id(),
            'date_mouvement' => now(),
            'nouvelle_valeur' => ['quantite' => $this->quantite]
        ]);
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
        
        $this->createMouvement('entree', $description ?? "Ajout de {$quantite} unités au stock");
        
        return $this;
    }

    /**
     * Diminuer la quantité en stock
     */
    public function retirerStock($quantite, $description = null)
    {
        if ($this->quantite < $quantite) {
            throw new \Exception("Stock insuffisant pour retirer {$quantite} unités.");
        }

        $this->decrement('quantite', $quantite);
        
        $this->createMouvement('sortie', $description ?? "Retrait de {$quantite} unités du stock");
        
        return $this;
    }

    /**
     * Vérifier si le stock est faible
     */
    public function isStockFaible()
    {
        return $this->quantite <= $this->seuil_alerte;
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