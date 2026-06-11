<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Itinerary extends Model
{
    use HasFactory;

    protected $table = 'itineraries';
    protected $primaryKey = 'Iditinerary';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'nom',
        'adresse_depart',
        'latitude_depart',
        'longitude_depart',
        'adresse_arrivee',
        'latitude_arrivee',
        'longitude_arrivee',
        'distance_km_estimee',
        'prix_estime',
        'Idagence',
        'Idsuccursale'
    ];

    protected $casts = [
        'latitude_depart' => 'decimal:8',
        'longitude_depart' => 'decimal:8',
        'latitude_arrivee' => 'decimal:8',
        'longitude_arrivee' => 'decimal:8',
        'distance_km_estimee' => 'decimal:2',
        'prix_estime' => 'decimal:2',
    ];

    public function agence()
    {
        return $this->belongsTo(Agence::class, 'Idagence', 'Idagence');
    }

    public function succursale()
    {
        return $this->belongsTo(Succursale::class, 'Idsuccursale', 'Idsuccursale');
    }

    public function courses()
    {
        return $this->hasMany(Course::class, 'Iditinerary', 'Iditinerary');
    }
}
