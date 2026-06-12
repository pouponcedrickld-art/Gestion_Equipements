<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'matricule',
        'nom',
        'prenom',
        'telephone',
        'email',
        'poste',
        'statut',
        'photo',
        'user_id',
    ];

    /**
     * Génère un matricule dynamique lors de la création
     */
    protected static function booted()
    {
        static::creating(function ($agent) {
            if (empty($agent->matricule)) {
                $agent->matricule = static::generateMatricule();
            }
        });
    }

    public static function generateMatricule()
    {
        $prefix = 'AGT';
        $year = date('Y');
        
        // On cherche le dernier matricule qui correspond au format AGT-YEAR-XXXX
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
            // pour voir si on peut en déduire une suite, mais le format préconisé est par année.
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

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function affectations()
    {
        return $this->hasMany(Affectation::class);
    }

    public function mouvements()
    {
        return $this->hasMany(Mouvement::class);
    }

    public function pannes()
    {
        return $this->hasMany(Panne::class);
    }

    public function pertes()
    {
        return $this->hasMany(Perte::class);
    }
}