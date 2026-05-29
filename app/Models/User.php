<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'name',
        'prenom',
        'email',
        'telephone',
        'password',
        'role_enum',
        'photo',
        'Idagence',
        'Idsuccursale',
        'est_actif'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'est_actif' => 'boolean',
    ];

    // Relations MOTOKA
    public function agence()
    {
        return $this->belongsTo(Agence::class, 'Idagence', 'Idagence');
    }

    public function succursale()
    {
        return $this->belongsTo(Succursale::class, 'Idsuccursale', 'Idsuccursale');
    }

    public function client()
    {
        return $this->hasOne(Client::class, 'Idutilisateur', 'id');
    }

    public function chauffeur()
    {
        return $this->hasOne(Chauffeur::class, 'Idutilisateur', 'id');
    }

    public function tickets()
    {
        return $this->hasMany(TicketSupp::class, 'Idutilisateur', 'id');
    }

    public function logsAudit()
    {
        return $this->hasMany(LogsAudit::class, 'Idutilisateur', 'id');
    }

    public function notifications()
    {
        return $this->belongsToMany(Notification::class, 'recoit', 'Idutilisateur', 'IdNotification')
                    ->withPivot('lu', 'lu_at', 'recu_at')
                    ->withTimestamps();
    }
}