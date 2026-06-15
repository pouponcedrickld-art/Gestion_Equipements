<?php

// =============================================
// MODÈLE : Agent
// RÔLE : Représente un agent (employé) qui peut être affecté à des équipements
// =============================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agent extends Model
{
    // Traits : pour les factories (tests) et pour le soft delete (suppression non permanente)
    use HasFactory, SoftDeletes;

    // Champs qui peuvent être remplis en masse
    protected $fillable = [
        'matricule', // Matricule unique de l'agent
        'nom', // Nom de l'agent
        'prenom', // Prénom de l'agent
        'telephone', // Numéro de téléphone
        'email', // Email de l'agent
        'poste', // Poste de l'agent
        'statut', // Statut (actif, inactif)
        'photo', // Photo de l'agent
        'user_id', // ID de l'utilisateur associé (si l'agent a un compte)
    ];

    /**
     * Hook qui s'exécute lors de la création d'un agent
     * Génère automatiquement un matricule si aucun n'est fourni
     */
    protected static function booted()
    {
        static::creating(function ($agent) {
            if (empty($agent->matricule)) {
                $agent->matricule = static::generateMatricule();
            }
        });
    }

    /**
     * Génère un matricule unique pour l'agent
     * Format : AGT-YYYY-XXXX (ex: AGT-2024-0001)
     */
    public static function generateMatricule()
    {
        $prefix = 'AGT';
        $year = date('Y');

        // Cherche le dernier matricule pour l'année en cours
        $lastAgent = static::where('matricule', 'like', "$prefix-$year-%")
            ->orderBy('matricule', 'desc')
            ->first();

        $number = 1;
        if ($lastAgent && $lastAgent->matricule) {
            $parts = explode('-', $lastAgent->matricule);
            if (count($parts) === 3) {
                $number = (int) $parts[2] + 1;
            }
        } else {
            // Si aucun matricule pour l'année en cours, on regarde le dernier globalement
            $lastAny = static::where('matricule', 'like', "$prefix-%")
                ->orderBy('id', 'desc')
                ->first();

            if ($lastAny && $lastAny->matricule) {
                $parts = explode('-', $lastAny->matricule);
                // Si c'est l'ancien format AGT-XXX
                if (count($parts) === 2) {
                    $number = (int) $parts[1] + 1;
                }
            }
        }

        return sprintf('%s-%s-%04d', $prefix, $year, $number);
    }

    // =============================================
    // RELATIONS
    // =============================================

    // Relation : Un agent est associé à UN utilisateur (si il a un compte)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relation : Un agent a PLUSIEURS affectations
    public function affectations()
    {
        return $this->hasMany(Affectation::class);
    }

    // Relation : Un agent a PLUSIEURS mouvements
    public function mouvements()
    {
        return $this->hasMany(Mouvement::class);
    }

    // Relation : Un agent a PLUSIEURS pannes (déclarées par lui)
    public function pannes()
    {
        return $this->hasMany(Panne::class);
    }

    // Relation : Un agent a PLUSIEURS pertes (déclarées par lui)
    public function pertes()
    {
        return $this->hasMany(Perte::class);
    }
}
