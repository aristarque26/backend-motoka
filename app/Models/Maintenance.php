<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
    use HasFactory;

    protected $table = 'maintenances';
    protected $primaryKey = 'IdMaintenance';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'Date_maintenance',
        'Kilometrage',
        'Type_Enum',
        'CoutMaintenance',
        'Description',
        'Facture_url',
        'Idvehicule'
    ];

    protected $casts = [
        'Date_maintenance' => 'datetime',
        'CoutMaintenance' => 'decimal:2',
    ];

    // Relations
    public function vehicule()
    {
        return $this->belongsTo(Vehicule::class, 'Idvehicule', 'Idvehicule');
    }

    public function depense()
    {
        return $this->hasOne(Depense::class, 'IdMaintenance', 'IdMaintenance');
    }

    public function alerteMaintenance()
    {
        return $this->hasOne(AlerteMaintenance::class, 'IdMaintenance', 'IdMaintenance');
    }
}