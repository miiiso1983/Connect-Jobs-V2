<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterSetting extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'setting_type','value'
    ];
}

