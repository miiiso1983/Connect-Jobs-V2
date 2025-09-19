<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','q','province','industry','job_title','frequency','channel','enabled','last_sent_at',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'last_sent_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $alert) {
            if (empty($alert->unsubscribe_token)) {
                $alert->unsubscribe_token = bin2hex(random_bytes(16));
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

