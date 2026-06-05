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
        'direction',
        'service',
        'poste',
        'statut',
        'photo',
        'user_id',
    ];

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