<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';
    protected $primaryKey = 'IdNotification';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'titre',
        'message',
        'canal_ENUM',
        'destinataire',
        'statutNotification_ENUM',
        'Idagence'
    ];

    // Relations
    public function agence()
    {
        return $this->belongsTo(Agence::class, 'Idagence', 'Idagence');
    }

    public function utilisateurs()
    {
        return $this->belongsToMany(User::class, 'recoit', 'IdNotification', 'Idutilisateur')
                    ->withTimestamps();
    }
}