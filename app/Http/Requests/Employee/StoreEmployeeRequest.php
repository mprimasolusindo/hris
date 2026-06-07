<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Employee::class);
    }

    public function rules(): array
    {
        return [
            'company_id' => ['required', 'exists:org_companies,id'],
            'employee_code' => ['required', 'string', 'max:255'],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'gender' => ['nullable', 'string', 'in:male,female'],
            'birth_date' => ['nullable', 'date'],
            'marital_status' => ['nullable', 'string', 'in:single,married,divorced,widowed'],
            'religion' => ['nullable', 'string', 'max:64'],
            'status' => ['required', 'string', 'max:32'],
            'join_date' => ['nullable', 'date'],
            'resign_date' => ['nullable', 'date'],
        ];
    }
}
