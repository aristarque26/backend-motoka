<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chauffeur extends Model
{
    use HasFactory;

    protected $table = 'chauffeurs';
    protected $primaryKey = 'Idchauffeur';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'nomChauffeur',
        'telephone',
        'adresse',
        'numeroPermis',
        'Url_Permis',
        'photo',
        'DateValidite',
        'statut_Enum',
        'type_contrat',
        'salaireBase',
        'DateEmbauche',
        'commission',
        'revenu',
        'NbreCourse',
        'NoteMoyenne',
        'Idutilisateur',
        'Idagence'
    ];

    protected $casts = [
        'DateValidite' => 'datetime',
        'DateEmbauche' => 'datetime',
        'salaireBase' => 'decimal:2',
        'commission' => 'decimal:2',
        'revenu' => 'decimal:2',
        'NoteMoyenne' => 'decimal:2',
    ];

    // Relations
    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'Idutilisateur', 'id');
    }

    public function agence()
    {
        return $this->belongsTo(Agence::class, 'Idagence', 'Idagence');
    }

    public function courses()
    {
        return $this->hasMany(Course::class, 'Idchauffeur', 'Idchauffeur');
    }

    public function vehiculesPossedes()
    {
        return $this->hasMany(Vehicule::class, 'Idchauffeur', 'Idchauffeur');
    }

    public function salaires()
    {
        return $this->hasMany(SalaireChauffeur::class, 'Idchauffeur', 'Idchauffeur');
    }

    public function maintenances()
    {
        return $this->hasMany(Maintenance::class, 'Idchauffeur', 'Idchauffeur');
    }
}