<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'job_id','job_seeker_id','cv_file','matching_percentage','applied_at','status'
    ];

    public function job(){ return $this->belongsTo(Job::class); }
    public function jobSeeker(){ return $this->belongsTo(JobSeeker::class); }
}

