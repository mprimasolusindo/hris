<?php

namespace App\Http\Requests\Admin;

use App\Models\Role;
use App\Services\SuperAdminGuard;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('users.create');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['integer', Rule::exists('sys_roles', 'id')],
        ];
    }

    protected function passedValidation(): void
    {
        if (! $this->user()->can('users.assign-roles')) {
            abort(403, 'You do not have permission to assign roles.');
        }

        app(SuperAdminGuard::class)->ensureSuperAdminRemains($this->input('roles', []));
    }
}
