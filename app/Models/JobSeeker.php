<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobSeeker extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'full_name',
        'province',
        'districts',
        'job_title',
        'speciality',
        'specialities',
        'education_level',
        'experience_level',
        'gender',
        'own_car',
        'profile_completed',
        'cv_file',
        'cv_verified',
        'profile_image',
        'summary',
        'qualifications',
        'experiences',
        'languages',
        'skills',

        // Education
        'university_name',
        'college_name',
        'department_name',
        'graduation_year',
        'is_fresh_graduate',
    ];

    protected $casts = [
        'districts' => 'array',
        'specialities' => 'array',
        'own_car' => 'boolean',
        'profile_completed' => 'boolean',
        'cv_verified' => 'boolean',
        'is_fresh_graduate' => 'boolean',
        'graduation_year' => 'integer',
    ];

    public function user(){ return $this->belongsTo(User::class); }
    public function applications(){ return $this->hasMany(Application::class); }
}

