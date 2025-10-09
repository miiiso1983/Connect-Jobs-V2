<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'body',
        'data',
        'fcm_tokens',
        'status',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'data' => 'array',
        'fcm_tokens' => 'array',
        'sent_at' => 'datetime',
    ];

    // Notification types
    const TYPE_ADMIN_NEW_COMPANY = 'admin_new_company';
    const TYPE_COMPANY_NEW_APPLICATION = 'company_new_application';
    const TYPE_JOBSEEKER_CV_DOWNLOADED = 'jobseeker_cv_downloaded';

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_FAILED = 'failed';

    /**
     * Get the user that owns the notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get notifications by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get notifications by status
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get pending notifications
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to get sent notifications
     */
    public function scopeSent($query)
    {
        return $query->where('status', self::STATUS_SENT);
    }

    /**
     * Scope to get failed notifications
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Mark notification as sent
     */
    public function markAsSent(array $fcmTokens = []): void
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'fcm_tokens' => $fcmTokens,
            'sent_at' => now(),
            'error_message' => null,
        ]);
    }

    /**
     * Mark notification as failed
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Get all notification types
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_ADMIN_NEW_COMPANY,
            self::TYPE_COMPANY_NEW_APPLICATION,
            self::TYPE_JOBSEEKER_CV_DOWNLOADED,
        ];
    }

    /**
     * Get human-readable type name
     */
    public function getTypeNameAttribute(): string
    {
        return match($this->type) {
            self::TYPE_ADMIN_NEW_COMPANY => 'تسجيل شركة جديدة',
            self::TYPE_COMPANY_NEW_APPLICATION => 'طلب توظيف جديد',
            self::TYPE_JOBSEEKER_CV_DOWNLOADED => 'تم تحميل السيرة الذاتية',
            default => $this->type,
        };
    }
}
