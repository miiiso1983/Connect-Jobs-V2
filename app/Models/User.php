<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements MustVerifyEmail, JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'role' => $this->role,
            'status' => $this->status,
        ];
    }

    /**
     * Get the company associated with the user.
     */
    public function company()
    {
        return $this->hasOne(\App\Models\Company::class);
    }

    /**
     * Get the job seeker associated with the user.
     */
    public function jobSeeker()
    {
        return $this->hasOne(\App\Models\JobSeeker::class);
    }

    /**
     * Get the FCM tokens associated with the user.
     */
    public function fcmTokens()
    {
        return $this->hasMany(\App\Models\UserFcmToken::class);
    }

    /**
     * Get only active FCM tokens for the user.
     */
    public function activeFcmTokens()
    {
        return $this->hasMany(\App\Models\UserFcmToken::class)->active();
    }

    /**
     * Get the push notifications for the user.
     */
    public function pushNotifications()
    {
        return $this->hasMany(\App\Models\PushNotification::class);
    }

    public function adminPermission(): HasOne
    {
        return $this->hasOne(AdminPermission::class);
    }

    public function isMasterAdmin(): bool
    {
        return strtolower((string) ($this->email ?? '')) === 'mustafa@teamiapps.com';
    }

    public function hasAdminPermission(string $key): bool
    {
        if (($this->role ?? null) !== 'admin') {
            return false;
        }

        if ($this->isMasterAdmin()) {
            return true;
        }

        return (bool) ($this->adminPermission?->allows($key) ?? false);
    }
}
