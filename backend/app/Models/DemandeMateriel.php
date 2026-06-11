<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandeMateriel extends Model
{
    use HasFactory;

    protected $table = 'demandes_materiel';

    protected $fillable = [
        'agence_id',
        'chef_agence_id',
        'equipement_id',
        'quantite',
        'urgence',
        'motif',
        'date_souhaitee',
        'statut',
        'traite_par_id',
        'observations',
    ];

    protected $casts = [
        'date_souhaitee' => 'date',
    ];

    // Relations
    public function agence()
    {
        return $this->belongsTo(Agence::class);
    }

    public function chefAgence()
    {
        return $this->belongsTo(User::class, 'chef_agence_id');
    }

    public function equipement()
    {
        return $this->belongsTo(Equipement::class);
    }

    public function traitePar()
    {
        return $this->belongsTo(User::class, 'traite_par_id');
    }

    public function transferts()
    {
        return $this->hasMany(Transfert::class, 'demande_materiel_id');
    }
}