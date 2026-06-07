<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use App\Services\SuperAdminGuard;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('users.update');
    }

    public function rules(): array
    {
        /** @var User $user */
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['integer', Rule::exists('sys_roles', 'id')],
        ];
    }

    protected function passedValidation(): void
    {
        if (! $this->user()->can('users.assign-roles')) {
            abort(403, 'You do not have permission to assign roles.');
        }

        /** @var User $user */
        $user = $this->route('user');

        $guard = app(SuperAdminGuard::class);
        $roleIds = $this->input('roles', []);

        if ($this->user()->id === $user->id && $guard->userHasSuperAdmin($user)) {
            $superAdmin = $guard->superAdminRole();
            if ($superAdmin && ! in_array($superAdmin->id, $roleIds, true)) {
                abort(403, 'You cannot remove your own super admin role.');
            }
        }

        $guard->ensureSuperAdminRemains($roleIds, $user);
    }
}
