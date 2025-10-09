<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CvAccessLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_seeker_id',
        'company_id',
        'job_id',
        'access_type',
        'ip_address',
        'user_agent',
    ];

    // Access types
    const TYPE_DOWNLOAD = 'download';
    const TYPE_VIEW = 'view';

    /**
     * Get the job seeker that owns the CV.
     */
    public function jobSeeker(): BelongsTo
    {
        return $this->belongsTo(JobSeeker::class);
    }

    /**
     * Get the company that accessed the CV.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the job related to the CV access (if any).
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    /**
     * Scope to get downloads only
     */
    public function scopeDownloads($query)
    {
        return $query->where('access_type', self::TYPE_DOWNLOAD);
    }

    /**
     * Scope to get views only
     */
    public function scopeViews($query)
    {
        return $query->where('access_type', self::TYPE_VIEW);
    }

    /**
     * Scope to get access logs for a specific job seeker
     */
    public function scopeForJobSeeker($query, $jobSeekerId)
    {
        return $query->where('job_seeker_id', $jobSeekerId);
    }

    /**
     * Scope to get access logs for a specific company
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Get all access types
     */
    public static function getAccessTypes(): array
    {
        return [
            self::TYPE_DOWNLOAD,
            self::TYPE_VIEW,
        ];
    }
}
