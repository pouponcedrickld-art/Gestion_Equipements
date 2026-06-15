<?php

// =============================================
// MODÈLE : PanneStatusHistory
// RÔLE : Enregistre l'historique des changements de statut des pannes
// Permet de garder une trace de qui a fait quoi et quand
// =============================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PanneStatusHistory extends Model
{
    // Trait : pour les factories (tests)
    use HasFactory;

    /**
     * Nom de la table dans la base de données
     * (on le spécifie explicitement pour être sûr, même si Laravel le devine normalement)
     */
    protected $table = 'panne_status_histories';

    // Champs qui peuvent être remplis en masse
    protected $fillable = [
        'panne_id', // ID de la panne concernée
        'statut_ancien', // Statut avant le changement
        'statut_nouveau', // Statut après le changement
        'commentaire', // Commentaire sur le changement
        'action_realisee', // Action qui a été réalisée
        'cout_reparation', // Coût de la réparation (si applicable)
        'created_by', // ID de l'utilisateur qui a fait le changement
    ];

    // Casts : conversion auto des types
    protected $casts = [
        'cout_reparation' => 'decimal:2', // Convertit en décimal avec 2 décimales (pour l'argent)
    ];

    // =============================================
    // RELATIONS
    // =============================================

    // Relation : Une entrée d'historique appartient à UNE panne
    public function panne(): BelongsTo
    {
        return $this->belongsTo(Panne::class);
    }

    // Relation : Une entrée d'historique a été créée par UN utilisateur
    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
