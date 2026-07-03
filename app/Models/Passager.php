<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Passager extends Model
{
    use HasFactory;

    protected $table = 'passagers';
    protected $primaryKey = 'Idpassager';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'nomPassager',
        'telephone',
        'nombre_sieges',
        'tarif_paye',
        'devise',
        'Idcource',
        'Idagence'
    ];

    protected $casts = [
        'tarif_paye' => 'decimal:2',
    ];

    public function agence()
    {
        return $this->belongsTo(Agence::class, 'Idagence', 'Idagence');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'Idcource', 'Idcource');
    }

    public function colis()
    {
        return $this->hasMany(Colis::class, 'Idpassager', 'Idpassager');
    }
}
