<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicule extends Model
{
    use HasFactory;

    protected $table = 'vehicules';
    protected $primaryKey = 'Idvehicule';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'immatriculation',
        'TypeVehicule',
        'marque',
        'modele',
        'couleur',
        'annee',
        'Capacite',
        'statut_enum',
        'Date_Expir_Assurance',
        'visiteTech',
        'kilometrage',
        'Idagence'
    ];

    protected $casts = [
        'annee' => 'datetime',
        'Date_Expir_Assurance' => 'datetime',
        'visiteTech' => 'datetime',
    ];

    // Relations
    public function agence()
    {
        return $this->belongsTo(Agence::class, 'Idagence', 'Idagence');
    }

    public function maintenances()
    {
        return $this->hasMany(Maintenance::class, 'Idvehicule', 'Idvehicule');
    }

    public function alertesMaintenance()
    {
        return $this->hasMany(AlerteMaintenance::class, 'Idvehicule', 'Idvehicule');
    }

    public function trackingGPS()
    {
        return $this->hasMany(TrackingGPS::class, 'Idvehicule', 'Idvehicule');
    }

    public function courses()
    {
        return $this->hasMany(Course::class, 'Idvehicule', 'Idvehicule');
    }
}