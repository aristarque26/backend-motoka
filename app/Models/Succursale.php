<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Succursale extends Model
{
    use HasFactory;

    protected $table = 'succursales';
    protected $primaryKey = 'Idsuccursale';
    public $incrementing = true;

    protected $fillable = [
        'Idagence',
        'nom',
        'ville',
        'adresse',
        'telephone',
        'Idmanager',
    ];

    public function agence()
    {
        return $this->belongsTo(Agence::class, 'Idagence', 'Idagence');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'Idmanager', 'id');
    }

    public function vehicules()
    {
        return $this->hasMany(Vehicule::class, 'Idsuccursale', 'Idsuccursale');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'Idsuccursale', 'Idsuccursale');
    }
}
