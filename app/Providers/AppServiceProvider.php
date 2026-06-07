<?php

namespace App\Providers;

use App\Models\Employee;
use App\Models\Permission;
use App\Policies\EmployeePolicy;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        Employee::class => EmployeePolicy::class,
    ];
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($rootUrl = config('app.url')) {
            URL::forceRootUrl($rootUrl);
        }

        // Inertia expects plain arrays, not { data: [...] } wrappers on nested resources.
        JsonResource::withoutWrapping();

        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }

        Gate::before(function ($user, $ability) {
            if ($user && method_exists($user, 'hasRole') && $user->hasRole('super-admin')) {
                return true;
            }

            return null;
        });

        if (Schema::hasTable('sys_permissions')) {
            try {
                Permission::query()->pluck('key')->each(function (string $key) {
                    Gate::define($key, fn ($user) => $user->hasPermission($key));
                });
            } catch (\Throwable) {
                // Migration-safe: permissions table may exist but be empty during seeding.
            }
        }

        Vite::prefetch(concurrency: 3);
    }
}
