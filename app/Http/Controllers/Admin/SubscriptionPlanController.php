<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionPlanController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Saas/Plans/Index', [
            'plans' => SubscriptionPlan::query()
                ->withCount('subscriptions')
                ->orderBy('price')
                ->get()
                ->map(fn (SubscriptionPlan $plan) => [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'price' => (float) $plan->price,
                    'employee_limit' => $plan->employee_limit,
                    'subscriptions_count' => $plan->subscriptions_count,
                ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        SubscriptionPlan::query()->create($this->validateData($request));

        return redirect()->route('admin.saas.plans.index')->with('success', 'Plan created.');
    }

    public function update(Request $request, SubscriptionPlan $plan): RedirectResponse
    {
        $plan->update($this->validateData($request));

        return redirect()->route('admin.saas.plans.index')->with('success', 'Plan updated.');
    }

    public function destroy(SubscriptionPlan $plan): RedirectResponse
    {
        $plan->delete();

        return redirect()->route('admin.saas.plans.index')->with('success', 'Plan deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'employee_limit' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
