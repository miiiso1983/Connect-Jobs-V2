<?php

namespace App\Policies;

use App\Models\Job;
use App\Models\User;

class JobPolicy
{
    public function before(User $user): ?bool
    {
        return $user->role === 'admin' ? true : null;
    }

    public function manage(User $user, Job $job): bool
    {
        $companyId = $user->company?->id;
        return $user->role === 'company' && $companyId && $job->company_id === $companyId;
    }
}

