<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perte extends Model
{
    use HasFactory;

    protected $fillable = [
        'equipement_id',
        'agent_id',
        'type',
        'date_declaration',
        'description',
        'statut',
        'valide_par',
        'date_validation',
    ];

    protected $casts = [
        'date_declaration' => 'datetime',
        'date_validation' => 'datetime',
    ];

    // Relations
    public function equipement()
    {
        return $this->belongsTo(Equipement::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function validePar()
    {
        return $this->belongsTo(User::class, 'valide_par');
    }
}