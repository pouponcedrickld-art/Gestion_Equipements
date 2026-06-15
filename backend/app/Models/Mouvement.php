<?php

// =============================================
// MODÈLE : Mouvement
// RÔLE : Enregistre tous les mouvements (historique) des équipements et des consommables
// Permet de garder une traçabilité complète
// =============================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mouvement extends Model
{
    // Trait : pour les factories (tests)
    use HasFactory;

    // Champs qui peuvent être remplis en masse
    protected $fillable = [
        'type_mouvement', // Type de mouvement (transfert, changement d'état, entrée, sortie, panne, etc.)
        'equipement_id', // ID de l'équipement concerné (si applicable)
        'consommable_id', // ID du consommable concerné (si applicable)
        'agent_id', // ID de l'agent concerné (si applicable)
        'user_id', // ID de l'utilisateur qui a fait le mouvement
        'date_mouvement', // Date et heure du mouvement
        'ancienne_valeur', // Valeur avant le changement (tableau JSON)
        'nouvelle_valeur', // Valeur après le changement (tableau JSON)
        'description', // Description du mouvement
    ];

    // Casts : conversion auto des types
    protected $casts = [
        'date_mouvement' => 'datetime', // Convertit en objet datetime Carbon
        'ancienne_valeur' => 'array', // Convertit JSON en tableau PHP
        'nouvelle_valeur' => 'array', // Convertit JSON en tableau PHP
    ];

    // =============================================
    // RELATIONS
    // =============================================

    // Relation : Un mouvement concerne UN équipement (si applicable)
    public function equipement()
    {
        return $this->belongsTo(Equipement::class);
    }

    // Relation : Un mouvement concerne UN consommable (si applicable)
    public function consommable()
    {
        return $this->belongsTo(Consommable::class);
    }

    // Relation : Un mouvement concerne UN agent (si applicable)
    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    // Relation : Un mouvement a été fait par UN utilisateur
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
