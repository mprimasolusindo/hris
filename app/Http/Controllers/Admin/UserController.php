<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\SuperAdminGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('users.view');

        $search = (string) $request->query('search', '');

        $users = User::query()
            ->with('roles:id,name,slug')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->map(fn (Role $role) => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'slug' => $role->slug,
                ])->values()->all(),
                'created_at' => $user->created_at?->toDateTimeString(),
            ]);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('users.create');

        return Inertia::render('Admin/Users/Create', [
            'roles' => $this->roleOptions(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = User::query()->create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
            'email_verified_at' => now(),
        ]);

        $user->roles()->sync($request->validated('roles'));
        $user->clearPermissionCache();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User created.');
    }

    public function edit(User $user): Response
    {
        $this->authorize('users.update');

        $user->load('roles:id');

        return Inertia::render('Admin/Users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role_ids' => $user->roles->pluck('id')->all(),
            ],
            'roles' => $this->roleOptions(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $user->update([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
        ]);

        if ($request->filled('password')) {
            $user->update([
                'password' => Hash::make($request->validated('password')),
            ]);
        }

        $user->roles()->sync($request->validated('roles'));
        $user->clearPermissionCache();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('users.delete');

        if ($user->id === auth()->id()) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }

        app(SuperAdminGuard::class)->ensureNotLastSuperAdmin($user);

        $user->roles()->detach();
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User deleted.');
    }

    /**
     * @return array<int, array{id: int, name: string, slug: string}>
     */
    private function roleOptions(): array
    {
        return Role::query()
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->map(fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
                'slug' => $role->slug,
            ])
            ->all();
    }
}
