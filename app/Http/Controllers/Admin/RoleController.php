<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RoleController extends Controller
{
    public function index(): Response
    {
        $this->authorize('roles.view');

        $roles = Role::query()
            ->withCount(['users', 'permissions'])
            ->orderBy('name')
            ->get()
            ->map(fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
                'slug' => $role->slug,
                'description' => $role->description,
                'is_system' => $role->is_system,
                'users_count' => $role->users_count,
                'permissions_count' => $role->permissions_count,
            ]);

        return Inertia::render('Admin/Roles/Index', [
            'roles' => $roles,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('roles.create');

        return Inertia::render('Admin/Roles/Edit', [
            'role' => null,
            'permissionGroups' => $this->permissionGroups(),
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $role = Role::query()->create([
            'name' => $request->validated('name'),
            'slug' => $request->validated('slug'),
            'description' => $request->validated('description'),
            'is_system' => false,
        ]);

        $role->permissions()->sync($request->validated('permissions', []));

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role created.');
    }

    public function edit(Role $role): Response
    {
        $this->authorize('roles.update');

        $role->load('permissions:id');

        return Inertia::render('Admin/Roles/Edit', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'slug' => $role->slug,
                'description' => $role->description,
                'is_system' => $role->is_system,
                'permission_ids' => $role->permissions->pluck('id')->all(),
            ],
            'permissionGroups' => $this->permissionGroups(),
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $role->update([
            'name' => $request->validated('name'),
            'slug' => $request->validated('slug'),
            'description' => $request->validated('description'),
        ]);

        $role->permissions()->sync($request->validated('permissions', []));

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role updated.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->authorize('roles.delete');

        if ($role->is_system) {
            return back()->withErrors(['role' => 'System roles cannot be deleted.']);
        }

        if ($role->users()->exists()) {
            return back()->withErrors(['role' => 'Cannot delete a role that is assigned to users.']);
        }

        $role->permissions()->detach();
        $role->delete();

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role deleted.');
    }

    /**
     * @return array<string, array<int, array{id: int, key: string, name: string}>>
     */
    private function permissionGroups(): array
    {
        return Permission::query()
            ->orderBy('module')
            ->orderBy('key')
            ->get(['id', 'key', 'name', 'module'])
            ->groupBy('module')
            ->map(fn ($permissions) => $permissions->map(fn (Permission $permission) => [
                'id' => $permission->id,
                'key' => $permission->key,
                'name' => $permission->name,
            ])->values()->all())
            ->all();
    }
}
