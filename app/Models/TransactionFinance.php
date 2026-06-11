<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionFinance extends Model
{
    use HasFactory;

    protected $table = 'transactions_finances';
    protected $primaryKey = 'IdTransactionFinance';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'montant',
        'devise',
        'mode_paiement_Enum',
        'reference_paiement',
        'Type_Transaction_Enum',
        'description',
        'Date_Paiement',
        'Idcource',
        'Idagence',
        'Idchauffeur',
        'Idsuccursale'
    ];

    protected $casts = [
        'Date_Paiement' => 'datetime',
        'montant' => 'decimal:2',
    ];

    // Relations
    public function course()
    {
        return $this->belongsTo(Course::class, 'Idcource', 'Idcource');
    }

    public function agence()
    {
        return $this->belongsTo(Agence::class, 'Idagence', 'Idagence');
    }

    public function chauffeur()
    {
        return $this->belongsTo(Chauffeur::class, 'Idchauffeur', 'Idchauffeur');
    }

    public function succursale()
    {
        return $this->belongsTo(Succursale::class, 'Idsuccursale', 'Idsuccursale');
    }
}
