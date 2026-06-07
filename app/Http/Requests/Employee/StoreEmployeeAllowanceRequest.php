<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeAllowanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('employee'));
    }

    public function rules(): array
    {
        return [
            'component_id' => ['nullable', 'exists:cfg_salary_components,id'],
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'taxable' => ['boolean'],
            'effective_start' => ['nullable', 'date'],
            'effective_end' => ['nullable', 'date', 'after_or_equal:effective_start'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'recurring' => ['boolean'],
        ];
    }
}
