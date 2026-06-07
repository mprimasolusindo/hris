<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Saas/Subscriptions/Index', [
            'subscriptions' => Subscription::query()
                ->with(['tenant:id,name', 'plan:id,name,price'])
                ->latest()
                ->get()
                ->map(fn (Subscription $subscription) => [
                    'id' => $subscription->id,
                    'tenant_id' => $subscription->tenant_id,
                    'tenant_name' => $subscription->tenant?->name,
                    'plan_id' => $subscription->plan_id,
                    'plan_name' => $subscription->plan?->name,
                    'start_date' => $subscription->start_date?->toDateString(),
                    'end_date' => $subscription->end_date?->toDateString(),
                    'status' => $subscription->status,
                ]),
            'tenants' => Tenant::query()->orderBy('name')->get(['id', 'name']),
            'plans' => SubscriptionPlan::query()->orderBy('price')->get(['id', 'name', 'price']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Subscription::query()->create($this->validateData($request));

        return redirect()->route('admin.saas.subscriptions.index')->with('success', 'Subscription created.');
    }

    public function update(Request $request, Subscription $subscription): RedirectResponse
    {
        $subscription->update($this->validateData($request));

        return redirect()->route('admin.saas.subscriptions.index')->with('success', 'Subscription updated.');
    }

    public function destroy(Subscription $subscription): RedirectResponse
    {
        $subscription->delete();

        return redirect()->route('admin.saas.subscriptions.index')->with('success', 'Subscription deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request): array
    {
        return $request->validate([
            'tenant_id' => ['required', 'exists:sys_tenants,id'],
            'plan_id' => ['required', 'exists:sub_plans,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'in:active,trialing,past_due,cancelled,expired'],
        ]);
    }
}
