<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketSupp extends Model
{
    use HasFactory;

    protected $table = 'tickets_supps';
    protected $primaryKey = 'IdTicketSupp';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'numero_ticket',
        'sujet',
        'message',
        'priorite_ENUM',
        'statut_ENUM',
        'categorie',
        'resolution',
        'Idutilisateur'
    ];

    // Relations
    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'Idutilisateur', 'id');
    }
}