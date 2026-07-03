<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Abonnement extends Model
{
    use HasFactory;

    protected $table = 'abonnements';
    protected $primaryKey = 'IdAbonnement';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'nomAgence',
        'slug',
        'description',
        'prix_mensuel',
        'prix_annuel',
        'devise',
        'max_succursales',
        'max_vehicules',
        'max_utilisateurs'
    ];

    protected $casts = [
        'prix_mensuel' => 'decimal:2',
        'prix_annuel' => 'decimal:2',
    ];

    // Relations
    public function agences()
    {
        return $this->hasMany(Agence::class, 'IdAbonnement', 'IdAbonnement');
    }
}