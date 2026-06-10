<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Affectation extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id',
        'equipement_id',
        'date_affectation',
        'date_retour_prevu',
        'date_retour_effectif',
        'affecte_par',
        'etat_retour',
        'observations',
        'pv_remise_path',
        'statut',
    ];

    protected $casts = [
        'date_affectation' => 'datetime',
        'date_retour_prevu' => 'datetime',
        'date_retour_effectif' => 'datetime',
    ];

    // Relations
    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function equipement()
    {
        return $this->belongsTo(Equipement::class);
    }

    public function affectePar()
    {
        return $this->belongsTo(User::class, 'affecte_par');
    }
}