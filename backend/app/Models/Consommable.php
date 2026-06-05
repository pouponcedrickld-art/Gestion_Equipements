<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consommable extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'type',
        'equipement_id',
        'quantite',
    ];

    public function equipement()
    {
        return $this->belongsTo(Equipement::class);
    }
}