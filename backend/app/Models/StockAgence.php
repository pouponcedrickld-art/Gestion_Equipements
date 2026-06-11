<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockAgence extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'agence_id',
        'equipement_id',
        'categorie_id',
        'quantite',
        'quantite_disponible',
        'quantite_reservee',
        'date_derniere_mise_a_jour',
    ];

    protected $casts = [
        'date_derniere_mise_a_jour' => 'datetime',
        'quantite' => 'integer',
        'quantite_disponible' => 'integer',
        'quantite_reservee' => 'integer',
    ];

    // ===== RELATIONS =====

    /**
     * Relation : Stock appartient à une agence
     */
    public function agence()
    {
        return $this->belongsTo(Agence::class);
    }

    /**
     * Relation : Stock lié à un équipement (si applicable)
     */
    public function equipement()
    {
        return $this->belongsTo(Equipement::class);
    }

    /**
     * Relation : Stock lié à une catégorie (si applicable)
     */
    public function categorie()
    {
        return $this->belongsTo(Categorie::class);
    }

    // ===== SCOPES =====

    /**
     * Scope : Filtrer par agence
     */
    public function scopeByAgence($query, $agenceId)
    {
        return $query->where('agence_id', $agenceId);
    }

    /**
     * Scope : Filtrer par équipement
     */
    public function scopeByEquipement($query, $equipementId)
    {
        return $query->where('equipement_id', $equipementId);
    }

    /**
     * Scope : Filtrer par catégorie
     */
    public function scopeByCategorie($query, $categorieId)
    {
        return $query->where('categorie_id', $categorieId);
    }

    // ===== MÉTHODES UTILITAIRES =====

    /**
     * Vérifier si le stock est disponible pour une quantité donnée
     */
    public function estDisponible($quantiteDemandee)
    {
        return $this->quantite_disponible >= $quantiteDemandee;
    }
}
