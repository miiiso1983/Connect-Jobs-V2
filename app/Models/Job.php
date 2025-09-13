<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'company_id','title','speciality','specialities','description','requirements','province','districts','status','approved_by_admin','jd_file'
    ];

    protected $casts = [
        'districts' => 'array',
        'specialities' => 'array',
    ];

    public function company(){ return $this->belongsTo(Company::class); }
    public function applications(){ return $this->hasMany(Application::class); }
}

