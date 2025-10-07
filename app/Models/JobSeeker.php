<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobSeeker extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id','full_name','province','districts','job_title','speciality','specialities','education_level','experience_level','gender','own_car','profile_completed','cv_file','profile_image','summary','qualifications','experiences','languages','skills'
    ];

    protected $casts = [
        'districts' => 'array',
        'specialities' => 'array',
    ];

    public function user(){ return $this->belongsTo(User::class); }
    public function applications(){ return $this->hasMany(Application::class); }
}

