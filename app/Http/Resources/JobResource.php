<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobResource extends JsonResource
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
            'title' => $this->title,
            'speciality' => $this->speciality,
            'specialities' => $this->specialities,
            'description' => $this->description,
            'requirements' => $this->requirements,
            'province' => $this->province,
            'districts' => $this->districts,
            'status' => $this->status,
            'approved_by_admin' => $this->approved_by_admin,
            'admin_reject_reason' => $this->admin_reject_reason,
            'jd_file' => $this->jd_file ? asset('storage/' . $this->jd_file) : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Include company data when loaded
            'company' => $this->when($this->relationLoaded('company'), function () {
                return [
                    'id' => $this->company->id,
                    'company_name' => $this->company->company_name,
                    'scientific_office_name' => $this->company->scientific_office_name,
                    'province' => $this->company->province,
                    'industry' => $this->company->industry,
                    'profile_image' => $this->company->profile_image ? asset('storage/' . $this->company->profile_image) : null,
                    'user' => $this->when($this->company->relationLoaded('user'), [
                        'id' => $this->company->user->id,
                        'name' => $this->company->user->name,
                        'email' => $this->company->user->email,
                    ]),
                ];
            }),
            
            // Include applications count when available
            'applications_count' => $this->when(isset($this->applications_count), $this->applications_count),
        ];
    }
}
