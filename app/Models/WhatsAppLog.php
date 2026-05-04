<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppLog extends Model
{
    protected $table = 'whatsapp_logs';

    protected $fillable = [
        'user_id',
        'phone_number',
        'message_type',
        'template_sid',
        'twilio_sid',
        'status',
        'error_message',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get human-readable message type label.
     */
    public function getTypeLabel(): string
    {
        return match ($this->message_type) {
            'profile_update' => 'تذكير إكمال البيانات',
            'cv_upload' => 'تذكير رفع CV',
            'custom' => 'رسالة مخصصة',
            default => $this->message_type,
        };
    }
}
