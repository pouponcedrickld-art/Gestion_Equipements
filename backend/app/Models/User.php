<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'agence_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relations
    public function agence()
    {
        return $this->belongsTo(Agence::class);
    }

    public function agent()
    {
        return $this->hasOne(Agent::class);
    }

    public function loginHistories()
    {
        return $this->hasMany(LoginHistory::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function pannesGerees()
    {
        return $this->hasMany(Panne::class, 'gestionnaire_stock_id');
    }

    public function pannesDiagnosticquees()
    {
        return $this->hasMany(Panne::class, 'technicien_id');
    }

    public function maintenances()
    {
        return $this->hasMany(Maintenance::class, 'technicien_id');
    }

    public function transfertsDemandes()
    {
        return $this->hasMany(Transfert::class, 'demande_par_id');
    }

    public function transfertsValides()
    {
        return $this->hasMany(Transfert::class, 'valide_par_id');
    }

    public function demandesMateriel()
    {
        return $this->hasMany(DemandeMateriel::class, 'chef_agence_id');
    }

    public function demandesMaterielTraitees()
    {
        return $this->hasMany(DemandeMateriel::class, 'traite_par_id');
    }
}