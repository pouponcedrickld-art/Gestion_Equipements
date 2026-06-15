<?php

// =============================================
// MODÈLE : DemandeMateriel
// RÔLE : Représente une demande de matériel faite par une agence
// =============================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandeMateriel extends Model
{
    // Trait : pour les factories (tests)
    use HasFactory;

    // Nom de la table dans la base de données
    protected $table = 'demandes_materiel';

    // Champs qui peuvent être remplis en masse
    protected $fillable = [
        'agence_id', // ID de l'agence qui fait la demande
        'chef_agence_id', // ID du chef d'agence qui valide la demande
        'equipement_id', // ID de l'équipement demandé
        'quantite', // Quantité demandée
        'urgence', // Niveau d'urgence (basse, moyenne, haute)
        'motif', // Motif de la demande
        'date_souhaitee', // Date souhaitée pour recevoir le matériel
        'statut', // Statut de la demande (en attente, approuvée, refusée, traitée)
        'traite_par_id', // ID de l'utilisateur qui a traité la demande
        'observations', // Observations sur la demande
    ];

    // Casts : conversion auto des types
    protected $casts = [
        'date_souhaitee' => 'date', // Convertit en objet date Carbon
    ];

    // =============================================
    // RELATIONS
    // =============================================

    // Relation : Une demande de matériel appartient à UNE agence
    public function agence()
    {
        return $this->belongsTo(Agence::class);
    }

    // Relation : Une demande de matériel a été validée par UN chef d'agence (utilisateur)
    public function chefAgence()
    {
        return $this->belongsTo(User::class, 'chef_agence_id');
    }

    // Relation : Une demande de matériel concerne UN équipement
    public function equipement()
    {
        return $this->belongsTo(Equipement::class);
    }

    // Relation : Une demande de matériel a été traitée par UN utilisateur
    public function traitePar()
    {
        return $this->belongsTo(User::class, 'traite_par_id');
    }

    // Relation : Une demande de matériel peut générer PLUSIEURS transferts
    public function transferts()
    {
        return $this->hasMany(Transfert::class, 'demande_materiel_id');
    }
}
