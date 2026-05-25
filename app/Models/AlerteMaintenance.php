<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlerteMaintenance extends Model
{
    use HasFactory;

    protected $table = 'alertes_maintenance';
    protected $primaryKey = 'IdAlertemaintenance';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'type_alerte',
        'seuil_valeur',
        'message',
        'date_alerte',
        'Idvehicule'
    ];

    protected $casts = [
        'date_alerte' => 'datetime',
    ];

    // Relations
    public function vehicule()
    {
        return $this->belongsTo(Vehicule::class, 'Idvehicule', 'Idvehicule');
    }
}
