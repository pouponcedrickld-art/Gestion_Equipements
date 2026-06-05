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
        'type_mission',
        'equipements_demandes',
        'date_besoin',
        'statut',
        'traite_par_id',
        'observations',
    ];

    protected $casts = [
        'equipements_demandes' => 'array',
        'date_besoin' => 'date',
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

    public function traitePar()
    {
        return $this->belongsTo(User::class, 'traite_par_id');
    }
}