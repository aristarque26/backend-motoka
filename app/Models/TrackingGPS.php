<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackingGPS extends Model
{
    use HasFactory;

    protected $table = 'tracking_gps';
    protected $primaryKey = 'IdTracking';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'Latitude',
        'Longitude',
        'Vitesse',
        'Position_Km',
        'altitude',
        'precisionGPS',
        'angle',
        'Idvehicule'
    ];

    protected $casts = [
        'Latitude' => 'decimal:8',
        'Longitude' => 'decimal:8',
        'Vitesse' => 'decimal:2',
        'altitude' => 'decimal:2',
        'angle' => 'decimal:2',
    ];

    // Relations
    public function vehicule()
    {
        return $this->belongsTo(Vehicule::class, 'Idvehicule', 'Idvehicule');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'Idcource', 'Idcource');
    }
}
