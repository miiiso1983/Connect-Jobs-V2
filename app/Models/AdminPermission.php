<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'manage_companies',
        'manage_jobs',
        'manage_jobseekers',
        'manage_verifications',
        'manage_settings',
        'manage_districts',
        'manage_admin_users',
    ];

    /**
     * Permission key => DB column mapping
     */
    public const MAP = [
        'companies' => 'manage_companies',
        'jobs' => 'manage_jobs',
        'jobseekers' => 'manage_jobseekers',
        'verifications' => 'manage_verifications',
        'settings' => 'manage_settings',
        'districts' => 'manage_districts',
        'admin_users' => 'manage_admin_users',
    ];

    public static function labels(): array
    {
        return [
            'companies' => 'إدارة الشركات',
            'jobs' => 'إدارة الوظائف',
            'jobseekers' => 'إدارة الباحثين',
            'verifications' => 'طلبات التوثيق',
            'settings' => 'الإعدادات',
            'districts' => 'إدارة المناطق',
            'admin_users' => 'إدارة الأدمن',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function allows(string $key): bool
    {
        $column = self::MAP[$key] ?? null;
        if (!$column) {
            return false;
        }

        return (bool) ($this->{$column} ?? false);
    }

    public static function fullAccessPayload(): array
    {
        $payload = [];
        foreach (self::MAP as $column) {
            $payload[$column] = true;
        }
        return $payload;
    }
}
