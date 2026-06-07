<?php

namespace App\Http\Controllers\Talent;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\TalentPool;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TalentPoolController extends Controller
{
    private const READINESS = ['ready_now', 'ready_1_2_years', 'ready_3_plus_years'];

    private const POTENTIAL = ['low', 'medium', 'high'];

    public function index(): Response
    {
        $entries = TalentPool::query()
            ->with(['employee:id,full_name,employee_code'])
            ->latest()
            ->get()
            ->map(fn (TalentPool $entry) => [
                'id' => $entry->id,
                'employee_id' => $entry->employee_id,
                'employee_name' => $entry->employee?->full_name,
                'employee_code' => $entry->employee?->employee_code,
                'readiness' => $entry->readiness,
                'potential' => $entry->potential,
                'notes' => $entry->notes,
            ]);

        return Inertia::render('Talent/TalentPool/Index', [
            'items' => $entries,
            'employees' => $this->employeeOptions(),
            'readinessOptions' => self::READINESS,
            'potentialOptions' => self::POTENTIAL,
            'summary' => [
                'total' => $entries->count(),
                'highPotential' => $entries->where('potential', 'high')->count(),
                'readyNow' => $entries->where('readiness', 'ready_now')->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        TalentPool::query()->create($this->validated($request));

        return redirect()
            ->route('talent-pool.index')
            ->with('success', 'Talent pool entry created.');
    }

    public function update(Request $request, TalentPool $talentPool): RedirectResponse
    {
        $talentPool->update($this->validated($request));

        return redirect()
            ->route('talent-pool.index')
            ->with('success', 'Talent pool entry updated.');
    }

    public function destroy(TalentPool $talentPool): RedirectResponse
    {
        $talentPool->delete();

        return redirect()
            ->route('talent-pool.index')
            ->with('success', 'Talent pool entry deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'employee_id' => ['required', 'exists:emp_employees,id'],
            'readiness' => ['required', 'string', 'in:'.implode(',', self::READINESS)],
            'potential' => ['required', 'string', 'in:'.implode(',', self::POTENTIAL)],
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
