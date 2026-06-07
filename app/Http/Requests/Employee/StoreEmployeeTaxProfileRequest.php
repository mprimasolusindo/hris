<?php

namespace App\Http\Requests\Employee;

use App\Support\Indonesia\IdValidators;
use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeTaxProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('employee'));
    }

    public function rules(): array
    {
        return [
            'has_npwp' => ['boolean'],
            'npwp' => IdValidators::npwpRules(),
            'tax_status' => IdValidators::taxStatusRules(),
            'tax_method' => IdValidators::taxMethodRules(),
            'dependents_count' => ['integer', 'min:0', 'max:3'],
        ];
    }
}
