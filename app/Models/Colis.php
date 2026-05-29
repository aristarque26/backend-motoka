

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Colis extends Model
{
    use HasFactory;

    protected $table = 'colis';
    protected $primaryKey = 'Idcolis';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'nomExpediteur',
        'TelephoneExpedit',
        'nomDestinateur',
        'CodeColis',
        'Qr_code_Url',
        'Otp_genere',
        'Otp_valide',
        'statut_enum',
        'Signature_Url',
        'Description',
        'Poids',
        'Idclient',
        'Idagence'
    ];

    protected $casts = [
        'Otp_genere' => 'datetime',
        'Poids' => 'decimal:2',
    ];

    // Relations
    public function client()
    {
        return $this->belongsTo(Client::class, 'Idclient', 'Idclient');
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_colis', 'Idcolis', 'Idcource')
                    ->withPivot('date_transport')
                    ->withTimestamps();
    }

    public function otpValidations()
    {
        return $this->hasMany(OtpValidation::class, 'Idcolis', 'Idcolis');
    }
}