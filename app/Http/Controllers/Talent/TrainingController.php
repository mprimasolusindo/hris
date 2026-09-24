<?php

namespace App\Http\Controllers\Talent;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Training;
use App\Support\ListPaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TrainingController extends Controller
{
    private const STATUSES = ['planned', 'ongoing', 'completed', 'cancelled'];

    private const ENROLLMENT_STATUSES = ['registered', 'attended', 'completed', 'dropped'];

    public function index(Request $request): Response
    {
        $perPage = ListPaginator::resolvePerPage($request);

        return Inertia::render('Talent/Training/Index', [
            'items' => ListPaginator::paginate(
                Training::query()->withCount('employees')->latest('start_date'),
                $request,
            )->through(fn (Training $training) => [
                'id' => $training->id,
                'name' => $training->name,
                'description' => $training->description,
                'start_date' => $training->start_date?->toDateString(),
                'end_date' => $training->end_date?->toDateString(),
                'location' => $training->location,
                'status' => $training->status,
                'participants' => $training->employees_count,
            ]),
            'statuses' => self::STATUSES,
            'summary' => [
                'total' => Training::query()->count(),
                'ongoing' => Training::query()->where('status', 'ongoing')->count(),
                'completed' => Training::query()->where('status', 'completed')->count(),
            ],
            'filters' => [
                'per_page' => is_string($perPage) ? $perPage : (string) $perPage,
            ],
        ]);
    }

    public function show(Training $training): Response
    {
        $training->load(['employees:id,full_name,employee_code']);

        $enrolledIds = $training->employees->pluck('id')->all();

        return Inertia::render('Talent/Training/Show', [
            'training' => [
                'id' => $training->id,
                'name' => $training->name,
                'description' => $training->description,
                'start_date' => $training->start_date?->toDateString(),
                'end_date' => $training->end_date?->toDateString(),
                'location' => $training->location,
                'status' => $training->status,
            ],
            'participants' => $training->employees->map(fn (Employee $employee) => [
                'id' => $employee->id,
                'name' => $employee->full_name,
                'employee_code' => $employee->employee_code,
                'status' => $employee->pivot->status,
            ]),
            'employees' => Employee::query()
                ->whereNotIn('id', $enrolledIds)
                ->orderBy('full_name')
                ->get(['id', 'full_name'])
                ->map(fn (Employee $e) => ['id' => $e->id, 'name' => $e->full_name]),
            'enrollmentStatuses' => self::ENROLLMENT_STATUSES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Training::query()->create($this->validated($request));

        return redirect()
            ->route('training.index')
            ->with('success', 'Training created.');
    }

    public function update(Request $request, Training $training): RedirectResponse
    {
        $training->update($this->validated($request));

        return redirect()
            ->back()
            ->with('success', 'Training updated.');
    }

    public function destroy(Training $training): RedirectResponse
    {
        $training->delete();

        return redirect()
            ->route('training.index')
            ->with('success', 'Training deleted.');
    }

    public function assign(Request $request, Training $training): RedirectResponse
    {
        $data = $request->validate([
            'employee_id' => ['required', 'exists:emp_employees,id'],
            'status' => ['nullable', 'string', 'in:'.implode(',', self::ENROLLMENT_STATUSES)],
        ]);

        $training->employees()->syncWithoutDetaching([
            $data['employee_id'] => ['status' => $data['status'] ?? 'registered'],
        ]);

        return redirect()
            ->route('training.show', $training)
            ->with('success', 'Employee assigned to training.');
    }

    public function unassign(Training $training, Employee $employee): RedirectResponse
    {
        $training->employees()->detach($employee->id);

        return redirect()
            ->route('training.show', $training)
            ->with('success', 'Employee removed from training.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:'.implode(',', self::STATUSES)],
        ]);
    }
}
