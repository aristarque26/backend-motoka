<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogsAudit extends Model
{
    use HasFactory;

    protected $table = 'logs_audits';
    protected $primaryKey = 'IdLogsAudit';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'Idutilisateur',
        'action',
        'table_name',
        'record_id',
        'ancienne_valeur',
        'nouvelle_valeur',
        'ip_address',
        'user_agent'
    ];

    // Relations
    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'Idutilisateur', 'id');
    }
}