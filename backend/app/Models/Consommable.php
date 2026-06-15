<?php

// =============================================
// MODÈLE : Consommable
// RÔLE : Représente un consommable associé à un équipement (batterie, chargeur, câble, etc.)
// Gère le stock et les mouvements
// =============================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consommable extends Model
{
    // Trait : pour les factories (tests)
    use HasFactory;

    // Champs qui peuvent être remplis en masse
    protected $fillable = [
        'nom', // Nom du consommable
        'type', // Type de consommable
        'equipement_id', // ID de l'équipement associé
        'quantite', // Quantité en stock
        'seuil_alerte', // Seuil d'alerte pour le stock faible
    ];

    // Casts : conversion auto des types
    protected $casts = [
        'quantite' => 'integer', // Convertit en entier
        'seuil_alerte' => 'integer', // Convertit en entier
    ];

    // =============================================
    // RELATIONS
    // =============================================

    // Relation : Un consommable appartient à UN équipement
    public function equipement()
    {
        return $this->belongsTo(Equipement::class);
    }

    // Relation : Un consommable a PLUSIEURS mouvements
    public function mouvements()
    {
        return $this->hasMany(Mouvement::class);
    }

    // =============================================
    // MÉTHODES UTILITAIRES
    // =============================================

    // Crée un mouvement de stock pour ce consommable
    public function createMouvement($type, $description, $userId = null)
    {
        return $this->mouvements()->create([
            'type_mouvement' => $type, // Type de mouvement (entrée, sortie)
            'description' => $description, // Description du mouvement
            'user_id' => $userId ?? auth()->id(), // ID de l'utilisateur qui a fait le mouvement
            'date_mouvement' => now(), // Date du mouvement
            'nouvelle_valeur' => ['quantite' => $this->quantite] // Nouvelle valeur de la quantité
        ]);
    }

    // =============================================
    // SCOPES (filtres réutilisables)
    // =============================================

    // Scope : Filtrer les consommables par type
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Scope : Récupérer les consommables avec un stock faible
    public function scopeStockFaible($query, $seuil = 1)
    {
        return $query->where('quantite', '<=', $seuil);
    }

    // Scope : Rechercher un consommable par son nom
    public function scopeSearch($query, $term)
    {
        return $query->where('nom', 'like', '%' . $term . '%');
    }

    // Scope : Filtrer les consommables pour un équipement spécifique
    public function scopeForEquipement($query, $equipementId)
    {
        return $query->where('equipement_id', $equipementId);
    }

    // Scope : Filtrer les consommables par agence (via l'équipement associé)
    public function scopeByAgence($query, $agenceId)
    {
        return $query->whereHas('equipement', function($q) use ($agenceId) {
            $q->where('agence_actuelle_id', $agenceId);
        });
    }

    // =============================================
    // MÉTHODES DE GESTION DU STOCK
    // =============================================

    // Ajoute une quantité au stock et crée un mouvement
    public function ajouterStock($quantite, $description = null)
    {
        $this->increment('quantite', $quantite); // Incrémente la quantité

        // Crée un mouvement pour l'historique
        $this->createMouvement('entree', $description ?? "Ajout de {$quantite} unités au stock");

        return $this;
    }

    // Retire une quantité du stock et crée un mouvement
    public function retirerStock($quantite, $description = null)
    {
        // Vérifie si le stock est suffisant
        if ($this->quantite < $quantite) {
            throw new \Exception("Stock insuffisant pour retirer {$quantite} unités.");
        }

        $this->decrement('quantite', $quantite); // Décrémente la quantité

        // Crée un mouvement pour l'historique
        $this->createMouvement('sortie', $description ?? "Retrait de {$quantite} unités du stock");

        return $this;
    }

    // =============================================
    // ACCESSEURS ET VÉRIFICATIONS
    // =============================================

    // Vérifie si le stock est faible
    public function isStockFaible()
    {
        return $this->quantite <= $this->seuil_alerte;
    }

    // Accesseur pour "statut_stock" : retourne le statut du stock
    public function getStatutStockAttribute()
    {
        if ($this->quantite == 0) return 'rupture'; // Rupture de stock
        if ($this->quantite <= 1) return 'stock_faible'; // Stock faible
        if ($this->quantite <= 3) return 'stock_moyen'; // Stock moyen
        return 'stock_bon'; // Stock bon
    }

    // Retourne la liste des types de consommables disponibles
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
