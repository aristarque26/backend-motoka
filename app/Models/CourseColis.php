<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseColis extends Model
{
    use HasFactory;

    protected $table = 'course_colis';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'Idcource',
        'Idcolis',
        'date_transport',
        'statut_transport',
        'position_ordre'
    ];

    protected $casts = [
        'date_transport' => 'datetime',
    ];

    // Relations
    public function course()
    {
        return $this->belongsTo(Course::class, 'Idcource', 'Idcource');
    }

    public function colis()
    {
        return $this->belongsTo(Colis::class, 'Idcolis', 'Idcolis');
    }
}