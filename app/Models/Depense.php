<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Depense extends Model
{
    use HasFactory;

    protected $table = 'depenses';
    protected $primaryKey = 'IdDepense';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'Libelle',
        'Montant',
        'typeDepense_ENUM',
        'Date_Depense',
        'justificatif_url',
        'Idagence'
    ];

    protected $casts = [
        'Date_Depense' => 'datetime',
        'Montant' => 'decimal:2',
    ];

    // Relations
    public function agence()
    {
        return $this->belongsTo(Agence::class, 'Idagence', 'Idagence');
    }

    public function maintenance()
    {
        return $this->belongsTo(Maintenance::class, 'IdMaintenance', 'IdMaintenance');
    }
}