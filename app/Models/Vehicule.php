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
        'CapacitePassagers',
        'CapacitePoids',
        'VolumeBagages',
        'statut_enum',
        'Date_Expir_Assurance',
        'visiteTech',
        'kilometrage',
        'proprietaire_type',
        'commission_fixe_course',
        'Idchauffeur',
        'Idagence',
        'Idsuccursale'
    ];

    protected $casts = [
        'annee' => 'datetime',
        'Date_Expir_Assurance' => 'datetime',
        'visiteTech' => 'datetime',
        'CapacitePoids' => 'decimal:2',
        'VolumeBagages' => 'decimal:2',
        'commission_fixe_course' => 'decimal:2',
    ];

    // Relations
    public function agence()
    {
        return $this->belongsTo(Agence::class, 'Idagence', 'Idagence');
    }

    public function succursale()
    {
        return $this->belongsTo(Succursale::class, 'Idsuccursale', 'Idsuccursale');
    }

    public function proprietaire()
    {
        return $this->belongsTo(Chauffeur::class, 'Idchauffeur', 'Idchauffeur');
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