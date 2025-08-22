<?php

namespace App\Policies;

use App\Models\Job;
use App\Models\User;

class JobPolicy
{
    public function manage(User $user, Job $job): bool
    {
        $companyId = $user->company?->id;
        return $user->role === 'company' && $companyId && $job->company_id === $companyId;
    }
}

