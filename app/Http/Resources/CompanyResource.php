<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
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
            'company_name' => $this->company_name,
            'scientific_office_name' => $this->scientific_office_name,
            'company_job_title' => $this->company_job_title,
            'mobile_number' => $this->mobile_number,
            'province' => $this->province,
            'industry' => $this->industry,
            'subscription_plan' => $this->subscription_plan,
            'subscription_expiry' => $this->subscription_expiry,
            'subscription_expires_at' => $this->subscription_expires_at,
            'status' => $this->status,
            'profile_image' => $this->profile_image ? asset('storage/' . $this->profile_image) : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
