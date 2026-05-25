<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $table = 'courses';
    protected $primaryKey = 'Idcource';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'nomCourse',
        'AdresseDepart',
        'LatitudeDepart',
        'LongitudeDepart',
        'AdresseArrive',
        'LatitudeArrivee',
        'LongitudeArrive',
        'Distance_Km',
        'PrixEstime',
        'PrixReel',
        'statut_enum',
        'Idclient',
        'Idchauffeur',
        'Idvehicule'
    ];

    protected $casts = [
        'LatitudeDepart' => 'decimal:8',
        'LongitudeDepart' => 'decimal:8',
        'LatitudeArrivee' => 'decimal:8',
        'LongitudeArrive' => 'decimal:8',
        'Distance_Km' => 'decimal:2',
        'PrixEstime' => 'decimal:2',
        'PrixReel' => 'decimal:2',
    ];

    // Relations
    public function client()
    {
        return $this->belongsTo(Client::class, 'Idclient', 'Idclient');
    }

    public function chauffeur()
    {
        return $this->belongsTo(Chauffeur::class, 'Idchauffeur', 'Idchauffeur');
    }

    public function vehicule()
    {
        return $this->belongsTo(Vehicule::class, 'Idvehicule', 'Idvehicule');
    }

    public function colis()
    {
        return $this->belongsToMany(Colis::class, 'course_colis', 'Idcource', 'Idcolis')
                    ->withPivot('date_transport')
                    ->withTimestamps();
    }

    public function transactions()
    {
        return $this->hasMany(TransactionFinance::class, 'Idcource', 'Idcource');
    }
}