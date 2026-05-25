<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $table = 'clients';
    protected $primaryKey = 'Idclient';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'nomClient',
        'prenomClient',
        'emailClient',
        'telephoneClient',
        'DateInscription',
        'ville',
        'addresseClient',
        'typeClient_ENUM',
        'Idutilisateur'
    ];

    protected $casts = [
        'DateInscription' => 'datetime',
    ];

    // Relations
    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'Idutilisateur', 'id');
    }

    public function agence()
    {
        return $this->belongsTo(Agence::class, 'Idagence', 'Idagence');
    }

    public function courses()
    {
        return $this->hasMany(Course::class, 'Idclient', 'Idclient');
    }

    public function colis()
    {
        return $this->hasMany(Colis::class, 'Idclient', 'Idclient');
    }
}