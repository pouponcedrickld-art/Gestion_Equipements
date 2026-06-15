<?php

// =============================================
// MODÈLE : Categorie
// RÔLE : Représente une catégorie d'équipements (ex: Téléphones, Ordinateurs, Imprimantes)
// Peut avoir une hiérarchie (catégorie parent et sous-catégories)
// =============================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    // Trait : pour les factories (tests)
    use HasFactory;

    // Champs qui peuvent être remplis en masse
    protected $fillable = [
        'nom', // Nom de la catégorie
        'slug', // Slug (pour les URLs)
        'description', // Description de la catégorie
        'statut', // Statut (actif, inactif)
        'code', // Code unique de la catégorie
        'parent_id', // ID de la catégorie parente (si sous-catégorie)
        'frequence_maintenance', // Fréquence de maintenance (en jours)
        'duree_vie', // Durée de vie estimée (en mois/années)
        'attributs_personnalises', // Attributs personnalisés (JSON)
    ];

    // Casts : conversion auto des types
    protected $casts = [
        'attributs_personnalises' => 'array', // Convertit JSON en tableau PHP
        'frequence_maintenance' => 'integer', // Convertit en entier
        'duree_vie' => 'integer', // Convertit en entier
    ];

    // =============================================
    // RELATIONS
    // =============================================

    // Relation : Une catégorie appartient à UNE catégorie parente (si applicable)
    public function parent()
    {
        return $this->belongsTo(Categorie::class, 'parent_id');
    }

    // Relation : Une catégorie a PLUSIEURS sous-catégories
    public function enfants()
    {
        return $this->hasMany(Categorie::class, 'parent_id');
    }

    // Relation : Une catégorie a PLUSIEURS équipements
    public function equipements()
    {
        return $this->hasMany(Equipement::class);
    }

    // =============================================
    // SCOPES (filtres réutilisables)
    // =============================================

    // Scope : Filtrer les catégories par statut
    public function scopeByStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    // Scope : Rechercher une catégorie par nom, description, code ou slug
    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('nom', 'like', '%' . $term . '%')
              ->orWhere('description', 'like', '%' . $term . '%')
              ->orWhere('code', 'like', '%' . $term . '%')
              ->orWhere('slug', 'like', '%' . $term . '%');
        });
    }

    // Scope : Ne récupérer que les catégories qui ont au moins un équipement
    public function scopeWithEquipements($query)
    {
        return $query->has('equipements');
    }

    // =============================================
    // ACCESSEURS ET MÉTHODES UTILITAIRES
    // =============================================

    // Accesseur pour "nombre_equipements" : retourne le nombre d'équipements dans la catégorie
    public function getNombreEquipementsAttribute()
    {
        return $this->equipements()->count();
    }

    // Vérifie si la catégorie peut être supprimée (pas d'équipements associés)
    public function canBeDeleted()
    {
        return $this->equipements()->count() === 0;
    }
}
