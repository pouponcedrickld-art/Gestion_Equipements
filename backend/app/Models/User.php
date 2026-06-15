<?php

// =============================================
// MODÈLE : User
// RÔLE : Représente un utilisateur de l'application
// C'est le modèle central pour l'authentification (via Sanctum)
// =============================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // Permet d'utiliser des tokens API pour l'authentification (comme notre JWT-like token)
use Spatie\Permission\Traits\HasRoles; // Permet de gérer les rôles et permissions avec Spatie

class User extends Authenticatable
{
    // Traits (outils ajoutés au modèle) :
    use HasApiTokens, // Génère des tokens pour l'API (Sanctum)
        HasFactory, // Permet de créer des faux utilisateurs pour les tests
        Notifiable, // Permet d'envoyer des notifications à l'utilisateur
        HasRoles; // Gère les rôles/permissions (Spatie)

    // Guard pour Spatie (on utilise le guard 'api' pour nos requêtes)
    protected $guard_name = 'api';

    // Champs qui PEUVENT être remplis en masse (via create() ou fill())
    protected $fillable = [
        'name', // Nom de l'utilisateur
        'email', // Email (pour la connexion)
        'password', // Mot de passe (sera haché automatiquement)
        'agence_id', // ID de l'agence à laquelle appartient l'utilisateur
        'actif', // Si l'utilisateur est actif (peut se connecter ou non)
    ];

    // Champs CACHÉS quand on renvoie l'utilisateur en JSON (pour la sécurité)
    protected $hidden = [
        'password', // On ne renvoie JAMAIS le mot de passe
        'remember_token', // Token pour la fonction "se souvenir de moi"
    ];

    // Casts : convertit automatiquement les champs de la BDD en types PHP
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime', // Convertit en objet Carbon (date/heure)
            'password' => 'hashed', // Hache automatiquement le mot de passe
            'actif' => 'boolean', // Convertit en booléen (true/false)
        ];
    }

    // =============================================
    // RELATIONS (liaisons avec d'autres modèles)
    // =============================================

    // Relation : Un utilisateur appartient à UNE agence
    public function agence()
    {
        return $this->belongsTo(Agence::class);
    }

    // Relation : Un utilisateur a ZÉRO ou UN agent (si c'est un agent)
    public function agent()
    {
        return $this->hasOne(Agent::class);
    }

    // Relation : Un utilisateur a PLUSIEURS historiques de connexion
    public function loginHistories()
    {
        return $this->hasMany(LoginHistory::class);
    }

    // Relation : Un utilisateur a PLUSIEURS notifications
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    // Relation : Un utilisateur a PLUSIEURS pannes qu'il a gérées (si gestionnaire stock)
    public function pannesGerees()
    {
        return $this->hasMany(Panne::class, 'gestionnaire_stock_id');
    }

    // Relation : Un utilisateur a PLUSIEURS pannes qu'il a diagnostiquées (si technicien)
    public function pannesDiagnosticquees()
    {
        return $this->hasMany(Panne::class, 'technicien_id');
    }

    // Relation : Un utilisateur a PLUSIEURS maintenances qu'il a effectuées (si technicien)
    public function maintenances()
    {
        return $this->hasMany(Maintenance::class, 'technicien_id');
    }

    // Relation : Un utilisateur a PLUSIEURS transferts qu'il a demandés
    public function transfertsDemandes()
    {
        return $this->hasMany(Transfert::class, 'demande_par_id');
    }

    // Relation : Un utilisateur a PLUSIEURS transferts qu'il a validés
    public function transfertsValides()
    {
        return $this->hasMany(Transfert::class, 'valide_par_id');
    }

    // Relation : Un utilisateur a PLUSIEURS demandes de matériel (si chef d'agence)
    public function demandesMateriel()
    {
        return $this->hasMany(DemandeMateriel::class, 'chef_agence_id');
    }

    // Relation : Un utilisateur a PLUSIEURS demandes de matériel qu'il a traitées
    public function demandesMaterielTraitees()
    {
        return $this->hasMany(DemandeMateriel::class, 'traite_par_id');
    }
}
