<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'slug',
        'description',
        'statut',
        'code',
        'parent_id',
        'frequence_maintenance',
        'duree_vie',
        'attributs_personnalises',
    ];

    protected $casts = [
        'attributs_personnalises' => 'array',
        'frequence_maintenance' => 'integer',
        'duree_vie' => 'integer',
    ];

    // ===== RELATIONS =====
    
    /**
     * Relation : Catégorie parente
     */
    public function parent()
    {
        return $this->belongsTo(Categorie::class, 'parent_id');
    }

    /**
     * Relation : Sous-catégories
     */
    public function enfants()
    {
        return $this->hasMany(Categorie::class, 'parent_id');
    }

    /**
     * Relation : Une catégorie a plusieurs équipements
     */
    public function equipements()
    {
        return $this->hasMany(Equipement::class);
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
     * Scope : Recherche par nom, description ou code
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('nom', 'like', '%' . $term . '%')
              ->orWhere('description', 'like', '%' . $term . '%')
              ->orWhere('code', 'like', '%' . $term . '%')
              ->orWhere('slug', 'like', '%' . $term . '%');
        });
    }

    /**
     * Scope : Catégories ayant des équipements
     */
    public function scopeWithEquipements($query)
    {
        return $query->has('equipements');
    }

    // ===== MÉTHODES UTILITAIRES =====
    
    /**
     * Obtenir le nombre d'équipements dans cette catégorie
     */
    public function getNombreEquipementsAttribute()
    {
        return $this->equipements()->count();
    }

    /**
     * Vérifier si la catégorie peut être supprimée
     */
    public function canBeDeleted()
    {
        return $this->equipements()->count() === 0;
    }
}