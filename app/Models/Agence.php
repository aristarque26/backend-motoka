<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agence extends Model
{
    use HasFactory;

    protected $table = 'agences';
    protected $primaryKey = 'Idagence';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'nom',
        'slug',
        'email',
        'telephone',
        'adresse',
        'logo_url',
        'plan_enum',
        'statut_enum',
        'ExpirationDate',
        'password',
        'config_prix_km',
        'config_frais_adhesion',
        'config_commission_defaut',
        'couleur_primaire',
        'IdAbonnement'
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'ExpirationDate' => 'datetime',
        'config_prix_km' => 'decimal:2',
        'config_frais_adhesion' => 'decimal:2',
        'config_commission_defaut' => 'decimal:2',
    ];

    // Relations
    public function succursales()
    {
        return $this->hasMany(Succursale::class, 'Idagence', 'Idagence');
    }

    public function abonnement()
    {
        return $this->belongsTo(Abonnement::class, 'IdAbonnement', 'IdAbonnement');
    }

    public function vehicules()
    {
        return $this->hasMany(Vehicule::class, 'Idagence', 'Idagence');
    }

    public function chauffeurs()
    {
        return $this->hasMany(Chauffeur::class, 'Idagence', 'Idagence');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'Idagence', 'Idagence');
    }

    public function depenses()
    {
        return $this->hasMany(Depense::class, 'Idagence', 'Idagence');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'Idagence', 'Idagence');
    }

    public function clients()
    {
        return $this->hasMany(Client::class, 'Idagence', 'Idagence');
    }
}