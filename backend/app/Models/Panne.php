<?php

// =============================================
// MODÈLE : Panne
// RÔLE : Représente une panne déclarée sur un équipement
// =============================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Panne extends Model
{
    use HasFactory; // Pour les tests/factories

    // Champs qui peuvent être remplis en masse
    protected $fillable = [
        'equipement_id', // ID de l'équipement concerné
        'agent_id', // ID de l'agent qui a déclaré la panne
        'gestionnaire_stock_id', // ID du gestionnaire de stock qui a reçu la panne
        'technicien_id', // ID du technicien qui a diagnostiqué/résolu la panne
        'date_declaration', // Date de déclaration de la panne
        'description', // Description de la panne
        'niveau_gravite', // Niveau de gravité (mineure, majeure, critique)
        'photos', // Photos de la panne (tableau JSON)
        'statut', // Statut de la panne (déclarée, en cours, résolue, etc.)
        'diagnostic_technicien', // Diagnostic du technicien
        'action_realisee', // Action réalisée pour résoudre
        'cout_reparation', // Coût de la réparation
        'date_resolution', // Date de résolution
        'decision_finale', // Décision finale (ex: réparé, remplacé, etc.)
    ];

    // Casts : conversion auto des types
    protected $casts = [
        'date_declaration' => 'datetime', // Convertit en datetime Carbon
        'photos' => 'array', // Convertit JSON en tableau PHP
        'cout_reparation' => 'decimal:2', // Convertit en décimal avec 2 décimales
        'date_resolution' => 'datetime', // Convertit en datetime Carbon
    ];

    // =============================================
    // RELATIONS
    // =============================================

    // Relation : Une panne appartient à UN équipement
    public function equipement()
    {
        return $this->belongsTo(Equipement::class);
    }

    // Relation : Une panne appartient à UN agent (qui l'a déclarée)
    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    // Relation : Une panne appartient à UN gestionnaire de stock (qui l'a reçu)
    public function gestionnaireStock()
    {
        return $this->belongsTo(User::class, 'gestionnaire_stock_id');
    }

    // Relation : Une panne appartient à UN technicien (qui l'a résolue)
    public function technicien()
    {
        return $this->belongsTo(User::class, 'technicien_id');
    }

    // Relation : Une panne a PLUSIEURS maintenances associées
    public function maintenances()
    {
        return $this->hasMany(Maintenance::class);
    }

    // Relation : Une panne a PLUSIEURS historiques de statut (pour suivre les transitions)
    public function statusHistories()
    {
        return $this->hasMany(PanneStatusHistory::class)->orderBy('created_at', 'desc');
    }
}
