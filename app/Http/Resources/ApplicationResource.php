<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
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
            'job_id' => $this->job_id,
            'job_seeker_id' => $this->job_seeker_id,
            'cv_file' => $this->cv_file ? asset('storage/' . $this->cv_file) : null,
            'matching_percentage' => $this->matching_percentage,
            'applied_at' => $this->applied_at,
            'status' => $this->status,
            'notes' => $this->notes,
            'reviewed_at' => $this->reviewed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Include job data when loaded
            'job' => $this->when($this->relationLoaded('job'), function () {
                return [
                    'id' => $this->job->id,
                    'title' => $this->job->title,
                    'speciality' => $this->job->speciality,
                    'province' => $this->job->province,
                    'status' => $this->job->status,
                    'company' => $this->when($this->job->relationLoaded('company'), function () {
                        return [
                            'id' => $this->job->company->id,
                            'company_name' => $this->job->company->company_name,
                            'scientific_office_name' => $this->job->company->scientific_office_name,
                            'profile_image' => $this->job->company->profile_image ? asset('storage/' . $this->job->company->profile_image) : null,
                            'user' => $this->when($this->job->company->relationLoaded('user'), [
                                'id' => $this->job->company->user->id,
                                'name' => $this->job->company->user->name,
                            ]),
                        ];
                    }),
                ];
            }),
            
            // Include job seeker data when loaded
            'job_seeker' => $this->when($this->relationLoaded('jobSeeker'), function () {
                return [
                    'id' => $this->jobSeeker->id,
                    'full_name' => $this->jobSeeker->full_name,
                    'job_title' => $this->jobSeeker->job_title,
                    'speciality' => $this->jobSeeker->speciality,
                    'province' => $this->jobSeeker->province,
                    'education_level' => $this->jobSeeker->education_level,
                    'experience_level' => $this->jobSeeker->experience_level,
                    'gender' => $this->jobSeeker->gender,
                    'own_car' => $this->jobSeeker->own_car,
                    'profile_image' => $this->jobSeeker->profile_image ? asset('storage/' . $this->jobSeeker->profile_image) : null,
                    'user' => $this->when($this->jobSeeker->relationLoaded('user'), [
                        'id' => $this->jobSeeker->user->id,
                        'name' => $this->jobSeeker->user->name,
                        'email' => $this->jobSeeker->user->email,
                    ]),
                ];
            }),
        ];
    }
}
