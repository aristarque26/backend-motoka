<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recoit extends Model
{
    use HasFactory;

    protected $table = 'recoit';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'Idutilisateur',
        'IdNotification',
        'lu',
        'lu_at',
        'recu_at'
    ];

    protected $casts = [
        'lu' => 'boolean',
        'lu_at' => 'datetime',
        'recu_at' => 'datetime',
    ];

    // Relations
    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'Idutilisateur', 'id');
    }

    public function notification()
    {
        return $this->belongsTo(Notification::class, 'IdNotification', 'IdNotification');
    }
}