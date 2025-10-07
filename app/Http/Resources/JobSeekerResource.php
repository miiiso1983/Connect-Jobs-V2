<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobSeekerResource extends JsonResource
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
            'full_name' => $this->full_name,
            'job_title' => $this->job_title,
            'speciality' => $this->speciality,
            'specialities' => $this->specialities,
            'province' => $this->province,
            'districts' => $this->districts,
            'education_level' => $this->education_level,
            'experience_level' => $this->experience_level,
            'gender' => $this->gender,
            'own_car' => $this->own_car,
            'skills' => $this->skills,
            'summary' => $this->summary,
            'qualifications' => $this->qualifications,
            'experiences' => $this->experiences,
            'languages' => $this->languages,
            'profile_completed' => $this->profile_completed,
            'profile_image' => $this->profile_image ? asset('storage/' . $this->profile_image) : null,
            'cv_file' => $this->cv_file ? asset('storage/' . $this->cv_file) : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
