<?php

namespace App\Http\Controllers\Talent;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Position;
use App\Models\SuccessionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SuccessionPlanController extends Controller
{
    private const READINESS = ['ready_now', 'ready_1_2_years', 'ready_3_plus_years'];

    public function index(): Response
    {
        $plans = SuccessionPlan::query()
            ->with([
                'position:id,name',
                'successor:id,full_name',
                'incumbent:id,full_name',
            ])
            ->latest()
            ->get()
            ->map(fn (SuccessionPlan $plan) => [
                'id' => $plan->id,
                'position_id' => $plan->position_id,
                'position_name' => $plan->position?->name,
                'successor_id' => $plan->successor_id,
                'successor_name' => $plan->successor?->full_name,
                'incumbent_id' => $plan->incumbent_id,
                'incumbent_name' => $plan->incumbent?->full_name,
                'readiness' => $plan->readiness,
                'notes' => $plan->notes,
            ]);

        return Inertia::render('Talent/Succession/Index', [
            'items' => $plans,
            'positions' => Position::query()->orderBy('name')->get(['id', 'name']),
            'employees' => $this->employeeOptions(),
            'readinessOptions' => self::READINESS,
            'summary' => [
                'total' => $plans->count(),
                'readyNow' => $plans->where('readiness', 'ready_now')->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        SuccessionPlan::query()->create($this->validated($request));

        return redirect()
            ->route('succession.index')
            ->with('success', 'Succession plan created.');
    }

    public function update(Request $request, SuccessionPlan $successionPlan): RedirectResponse
    {
        $successionPlan->update($this->validated($request));

        return redirect()
            ->route('succession.index')
            ->with('success', 'Succession plan updated.');
    }

    public function destroy(SuccessionPlan $successionPlan): RedirectResponse
    {
        $successionPlan->delete();

        return redirect()
            ->route('succession.index')
            ->with('success', 'Succession plan deleted.');
    }

    private function validated(Request $request): array
    {
        if (in_array($request->input('incumbent_id'), ['none', ''], true)) {
            $request->merge(['incumbent_id' => null]);
        }

        return $request->validate([
            'position_id' => ['required', 'exists:org_positions,id'],
            'successor_id' => ['required', 'exists:emp_employees,id'],
            'incumbent_id' => ['nullable', 'exists:emp_employees,id', 'different:successor_id'],
            'readiness' => ['required', 'string', 'in:'.implode(',', self::READINESS)],
            'notes' => ['nullable', 'string'],
        ]);
    }

    private function employeeOptions()
    {
        return Employee::query()
            ->orderBy('full_name')
            ->get(['id', 'full_name'])
            ->map(fn (Employee $e) => ['id' => $e->id, 'name' => $e->full_name]);
    }
}
