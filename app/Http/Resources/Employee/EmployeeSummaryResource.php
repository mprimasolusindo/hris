<?php

namespace App\Http\Resources\Employee;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Employee */
class EmployeeSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_code' => $this->employee_code,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->status,
            'join_date' => $this->join_date?->toDateString(),
            'profile_photo_url' => $this->profile_photo_path
                ? asset('storage/'.$this->profile_photo_path)
                : null,
            'company_name' => $this->company?->name,
            'site_name' => $this->siteAssignments->first()?->site?->name,
            'department_name' => $this->jobs->first()?->department?->name,
        ];
    }
}
