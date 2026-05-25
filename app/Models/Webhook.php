<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    use HasFactory;

    protected $table = 'webhooks';
    protected $primaryKey = 'IdWebhooks';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'Idagence',
        'url',
        'evenements',
        'secret',
        'est_actif',
        'derniere_reponse',
        'derniere_erreur'
    ];

    protected $casts = [
        'evenements' => 'array',
        'est_actif' => 'boolean',
    ];

    // Relations
    public function agence()
    {
        return $this->belongsTo(Agence::class, 'Idagence', 'Idagence');
    }
}