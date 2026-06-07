<?php

namespace App\Http\Controllers\Talent;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\NineBoxAssessment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NineBoxController extends Controller
{
    /**
     * Box labels keyed by "performance-potential" (both 1..3).
     * Performance is the horizontal axis, potential the vertical axis.
     */
    public const BOX_LABELS = [
        '1-1' => 'Risk',
        '2-1' => 'Effective',
        '3-1' => 'Trusted Professional',
        '1-2' => 'Inconsistent Player',
        '2-2' => 'Core Player',
        '3-2' => 'High Performer',
        '1-3' => 'Potential Gem',
        '2-3' => 'High Potential',
        '3-3' => 'Star',
    ];

    public function index(Request $request): Response
    {
        $year = (int) $request->query('year', (string) now()->year);

        $assessments = NineBoxAssessment::query()
            ->with(['employee:id,full_name,employee_code'])
            ->where('period_year', $year)
            ->latest()
            ->get();

        $items = $assessments->map(fn (NineBoxAssessment $a) => [
            'id' => $a->id,
            'employee_id' => $a->employee_id,
            'employee_name' => $a->employee?->full_name,
            'employee_code' => $a->employee?->employee_code,
            'period_year' => $a->period_year,
            'performance_score' => $a->performance_score,
            'potential_score' => $a->potential_score,
            'box_label' => $a->box_label,
            'notes' => $a->notes,
        ]);

        // Build a 3x3 grid. Rows are potential 3..1 (top to bottom),
        // columns are performance 1..3 (left to right).
        $grid = [];
        foreach ([3, 2, 1] as $potential) {
            $row = [];
            foreach ([1, 2, 3] as $performance) {
                $key = $performance.'-'.$potential;
                $row[] = [
                    'performance' => $performance,
                    'potential' => $potential,
                    'label' => self::BOX_LABELS[$key],
                    'employees' => $items
                        ->where('performance_score', $performance)
                        ->where('potential_score', $potential)
                        ->values(),
                ];
            }
            $grid[] = $row;
        }

        $years = NineBoxAssessment::query()
            ->select('period_year')
            ->distinct()
            ->orderByDesc('period_year')
            ->pluck('period_year');

        if (! $years->contains($year)) {
            $years->push($year);
        }

        return Inertia::render('Talent/NineBox/Index', [
            'items' => $items,
            'grid' => $grid,
            'year' => $year,
            'years' => $years->sortDesc()->values(),
            'employees' => $this->employeeOptions(),
            'summary' => [
                'total' => $items->count(),
                'stars' => $items->where('box_label', 'Star')->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['box_label'] = $this->resolveLabel($data['performance_score'], $data['potential_score']);

        NineBoxAssessment::query()->updateOrCreate(
            [
                'employee_id' => $data['employee_id'],
                'period_year' => $data['period_year'],
            ],
            $data,
        );

        return redirect()
            ->route('succession.nine-box.index', ['year' => $data['period_year']])
            ->with('success', 'Nine-box assessment saved.');
    }

    public function update(Request $request, NineBoxAssessment $nineBox): RedirectResponse
    {
        $data = $this->validated($request);
        $data['box_label'] = $this->resolveLabel($data['performance_score'], $data['potential_score']);

        $nineBox->update($data);

        return redirect()
            ->route('succession.nine-box.index', ['year' => $data['period_year']])
            ->with('success', 'Nine-box assessment updated.');
    }

    public function destroy(NineBoxAssessment $nineBox): RedirectResponse
    {
        $nineBox->delete();

        return redirect()
            ->route('succession.nine-box.index', ['year' => $nineBox->period_year])
            ->with('success', 'Nine-box assessment deleted.');
    }

    private function resolveLabel(int $performance, int $potential): string
    {
        return self::BOX_LABELS[$performance.'-'.$potential] ?? '';
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'employee_id' => ['required', 'exists:emp_employees,id'],
            'period_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'performance_score' => ['required', 'integer', 'min:1', 'max:3'],
            'potential_score' => ['required', 'integer', 'min:1', 'max:3'],
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
