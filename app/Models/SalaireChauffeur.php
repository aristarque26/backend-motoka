<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaireChauffeur extends Model
{
    use HasFactory;

    protected $table = 'salaire_chauffeurs';
    protected $primaryKey = 'IdSalaireChauffeur';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'Mois',
        'Annee',
        'Montant_base',
        'Commission',
        'Primes',
        'Deduction',
        'Montant_total',
        'Statut_Salaire_ENUM',
        'Date_paiement',
        'nbre_courses',
        'revenu_course',
        'Idchauffeur'
    ];

    protected $casts = [
        'Date_paiement' => 'datetime',
        'Montant_base' => 'decimal:2',
        'Commission' => 'decimal:2',
        'Primes' => 'decimal:2',
        'Deduction' => 'decimal:2',
        'revenu_course' => 'decimal:2',
    ];

    // Relations
    public function chauffeur()
    {
        return $this->belongsTo(Chauffeur::class, 'Idchauffeur', 'Idchauffeur');
    }
}