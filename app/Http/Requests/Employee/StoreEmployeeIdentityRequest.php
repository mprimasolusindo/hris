<?php

namespace App\Http\Requests\Employee;

use App\Support\Indonesia\IdValidators;
use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeIdentityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('employee'));
    }

    public function rules(): array
    {
        return [
            'nik' => IdValidators::nikRules(),
            'npwp' => IdValidators::npwpRules(),
            'bpjs_health' => IdValidators::bpjsNumberRules(),
            'bpjs_employment' => IdValidators::bpjsNumberRules(),
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:64'],
        ];
    }
}
