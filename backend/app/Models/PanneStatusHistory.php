<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PanneStatusHistory extends Model
{
    use HasFactory;

    /**
     * Table name (par convention Laravel, mais on le fixe par sécurité).
     */
    protected $table = 'panne_status_histories';

    protected $fillable = [
        'panne_id',
        'statut_ancien',
        'statut_nouveau',
        'commentaire',
        'action_realisee',
        'cout_reparation',
        'created_by',
    ];

    protected $casts = [
        'cout_reparation' => 'decimal:2',
    ];

    /**
     * Panne associée.
     */
    public function panne(): BelongsTo
    {
        return $this->belongsTo(Panne::class);
    }

    /**
     * Auteur (user) de la transition.
     */
    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

