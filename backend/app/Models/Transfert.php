<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transfert extends Model
{
    use HasFactory;

    protected $fillable = [
        'equipement_id',
        'agence_source_id',
        'agence_destination_id',
        'type_transfert',
        'demande_par_id',
        'valide_par_id',
        'date_demande',
        'date_expedition',
        'date_reception',
        'statut',
        'quantite',
        'observations',
    ];

    protected $casts = [
        'date_demande' => 'datetime',
        'date_expedition' => 'datetime',
        'date_reception' => 'datetime',
    ];

    // Relations
    public function equipement()
    {
        return $this->belongsTo(Equipement::class);
    }

    public function agenceSource()
    {
        return $this->belongsTo(Agence::class, 'agence_source_id');
    }

    public function agenceDestination()
    {
        return $this->belongsTo(Agence::class, 'agence_destination_id');
    }

    public function demandePar()
    {
        return $this->belongsTo(User::class, 'demande_par_id');
    }

    public function validePar()
    {
        return $this->belongsTo(User::class, 'valide_par_id');
    }
}