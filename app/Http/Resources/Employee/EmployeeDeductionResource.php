<?php

namespace App\Http\Resources\Employee;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeDeductionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'component_id' => $this->component_id,
            'component_name' => $this->component?->name,
            'name' => $this->name,
            'value' => $this->value,
            'effective_start' => $this->effective_start?->toDateString(),
            'effective_end' => $this->effective_end?->toDateString(),
            'status' => $this->status,
            'recurring' => $this->recurring,
        ];
    }
}
