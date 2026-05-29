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
        'mode_paiement_Enum',
        'Type_Transaction_Enum',
        'Date_Paiement',
        'Idcource'
    ];

    protected $casts = [
        'Date_Paiement' => 'datetime',
        'montant' => 'integer',
    ];

    // Relations
    public function course()
    {
        return $this->belongsTo(Course::class, 'Idcource', 'Idcource');
    }
}
