<?php

// =============================================
// MODÈLE : Agence
// RÔLE : Représente une agence, un site ou une direction dans l'application
// Peut avoir une hiérarchie (agence principale et sous-agences)
// =============================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agence extends Model
{
    // Trait : pour les factories (tests)
    use HasFactory;

    // Champs qui peuvent être remplis en masse
    protected $fillable = [
        'type', // Type d'agence (principale, sous-agence, etc.)
        'parent_id', // ID de l'agence parente (si c'est une sous-agence)
        'nom', // Nom de l'agence
        'adresse', // Adresse
        'ville', // Ville
        'code_postal', // Code postal
        'telephone', // Numéro de téléphone
        'email', // Email de l'agence
        'responsable_id', // ID du responsable (utilisateur)
        'gestionnaire_stock_id', // ID du gestionnaire de stock (utilisateur)
        'statut', // Statut (actif, inactif)
    ];

    // =============================================
    // RELATIONS
    // =============================================

    // Relation : Une agence appartient à UNE agence parente (si applicable)
    public function parent()
    {
        return $this->belongsTo(Agence::class, 'parent_id');
    }

    // Relation : Une agence a PLUSIEURS sous-agences
    public function sousAgences()
    {
        return $this->hasMany(Agence::class, 'parent_id');
    }

    // Relation : Une agence a UN responsable (utilisateur)
    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    // Relation : Une agence a UN gestionnaire de stock (utilisateur)
    public function gestionnaireStock()
    {
        return $this->belongsTo(User::class, 'gestionnaire_stock_id');
    }

    // Relation : Une agence a PLUSIEURS utilisateurs
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Relation : Une agence est propriétaire de PLUSIEURS équipements
    public function equipementsProprietaires()
    {
        return $this->hasMany(Equipement::class, 'agence_proprietaire_id');
    }

    // Relation : Une agence a actuellement PLUSIEURS équipements (où ils se trouvent)
    public function equipementsActuels()
    {
        return $this->hasMany(Equipement::class, 'agence_actuelle_id');
    }

    // Relation : Une agence est source de PLUSIEURS transferts
    public function transfertsSource()
    {
        return $this->hasMany(Transfert::class, 'agence_source_id');
    }

    // Relation : Une agence est destination de PLUSIEURS transferts
    public function transfertsDestination()
    {
        return $this->hasMany(Transfert::class, 'agence_destination_id');
    }

    // Relation : Une agence a PLUSIEURS demandes de matériel
    public function demandesMateriel()
    {
        return $this->hasMany(DemandeMateriel::class);
    }
}
