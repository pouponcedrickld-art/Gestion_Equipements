<?php

// =============================================
// MODÈLE : Perte
// RÔLE : Représente une déclaration de perte ou de vol d'un équipement
// =============================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perte extends Model
{
    // Trait : pour les factories (tests)
    use HasFactory;

    // Champs qui peuvent être remplis en masse
    protected $fillable = [
        'equipement_id', // ID de l'équipement perdu ou volé
        'agent_id', // ID de l'agent qui déclare la perte
        'type', // Type de perte (perte, vol)
        'date_declaration', // Date et heure de la déclaration
        'description', // Description de la perte
        'statut', // Statut (en attente, validée, refusée)
        'valide_par', // ID de l'utilisateur qui valide la perte
        'date_validation', // Date et heure de la validation
    ];

    // Casts : conversion auto des types
    protected $casts = [
        'date_declaration' => 'datetime', // Convertit en objet datetime Carbon
        'date_validation' => 'datetime', // Convertit en objet datetime Carbon
    ];

    // =============================================
    // RELATIONS
    // =============================================

    // Relation : Une perte concerne UN équipement
    public function equipement()
    {
        return $this->belongsTo(Equipement::class);
    }

    // Relation : Une perte est déclarée par UN agent
    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    // Relation : Une perte est validée par UN utilisateur
    public function validePar()
    {
        return $this->belongsTo(User::class, 'valide_par');
    }
}
