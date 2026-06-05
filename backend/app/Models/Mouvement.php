<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mouvement extends Model
{
    use HasFactory;

    protected $fillable = [
        'type_mouvement',
        'equipement_id',
        'agent_id',
        'user_id',
        'date_mouvement',
        'ancienne_valeur',
        'nouvelle_valeur',
        'description',
    ];

    protected $casts = [
        'date_mouvement' => 'datetime',
        'ancienne_valeur' => 'array',
        'nouvelle_valeur' => 'array',
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}