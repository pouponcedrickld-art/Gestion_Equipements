<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GarantieAlerte extends Model
{
    protected $fillable = [
        'equipement_id',
        'seuil_jours',
        'date_envoi',
    ];

    protected $casts = [
        'date_envoi' => 'datetime',
    ];

    // ===== RELATIONS =====
    public function equipement()
    {
        return $this->belongsTo(Equipement::class);
    }
}
