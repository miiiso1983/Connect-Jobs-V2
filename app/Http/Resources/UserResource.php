<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'status' => $this->status,
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Include role-specific profile data
            'company' => $this->when($this->role === 'company' && $this->relationLoaded('company'), 
                new CompanyResource($this->company)
            ),
            'job_seeker' => $this->when($this->role === 'jobseeker' && $this->relationLoaded('jobSeeker'), 
                new JobSeekerResource($this->jobSeeker)
            ),
        ];
    }
}
