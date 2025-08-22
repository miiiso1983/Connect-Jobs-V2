<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id','company_name','scientific_office_name','company_job_title','mobile_number','province','industry','subscription_plan','subscription_expiry','status'
    ];

    public function user(){ return $this->belongsTo(User::class); }
    public function jobs(){ return $this->hasMany(Job::class); }
}

