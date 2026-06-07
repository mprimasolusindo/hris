<?php

namespace App\Http\Controllers\Overtime;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Overtime;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OvertimeController extends Controller
{
    public const STATUSES = ['pending', 'approved', 'rejected'];

    public function index(Request $request): Response
    {
        $status = (string) $request->query('status', '');
        $employeeId = $request->query('employee_id');

        $overtimes = Overtime::query()
            ->with(['employee:id,full_name,employee_code', 'approver:id,full_name'])
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->when($employeeId, fn ($q) => $q->where('employee_id', $employeeId))
            ->orderByDesc('date')
            ->paginate(20)
            ->withQueryString();

        $overtimes->getCollection()->transform(fn (Overtime $row) => $this->serialize($row));

        $summary = [
            'pending' => Overtime::query()->where('status', 'pending')->count(),
            'approved' => Overtime::query()->where('status', 'approved')->count(),
            'rejected' => Overtime::query()->where('status', 'rejected')->count(),
        ];

        return Inertia::render('Overtime/Index', [
            'overtimes' => $overtimes,
            'filters' => [
                'status' => $status,
                'employee_id' => $employeeId ? (string) $employeeId : '',
            ],
            'summary' => $summary,
            'statusOptions' => self::STATUSES,
            'employees' => Employee::query()
                ->where('status', 'active')
                ->orderBy('full_name')
                ->get(['id', 'full_name', 'employee_code']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'employee_id' => ['required', 'exists:emp_employees,id'],
            'date' => ['required', 'date'],
            'hours' => ['required', 'numeric', 'min:0', 'max:24'],
        ]);

        Overtime::query()->create($data + ['status' => 'pending']);

        return redirect()->route('overtime.index')->with('success', 'Overtime claim submitted.');
    }

    public function update(Request $request, Overtime $overtime): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', self::STATUSES)],
            'hours' => ['sometimes', 'numeric', 'min:0', 'max:24'],
        ]);

        $patch = ['status' => $data['status']];

        if (array_key_exists('hours', $data)) {
            $patch['hours'] = $data['hours'];
        }

        if ($data['status'] === 'approved') {
            $patch['approved_by'] = $this->currentEmployeeId();
        } elseif ($data['status'] === 'rejected' || $data['status'] === 'pending') {
            $patch['approved_by'] = null;
        }

        $overtime->update($patch);

        return redirect()->route('overtime.index')->with('success', 'Overtime claim updated.');
    }

    public function destroy(Overtime $overtime): RedirectResponse
    {
        $overtime->delete();

        return redirect()->route('overtime.index')->with('success', 'Overtime claim removed.');
    }

    /**
     * approved_by is an FK to emp_employees; resolve the acting user's
     * linked employee record if one exists (null otherwise).
     */
    private function currentEmployeeId(): ?int
    {
        $userId = auth()->id();

        if (! $userId) {
            return null;
        }

        return Employee::query()->where('user_id', $userId)->value('id');
    }

    private function serialize(Overtime $overtime): array
    {
        return [
            'id' => $overtime->id,
            'employee_id' => $overtime->employee_id,
            'employee_name' => $overtime->employee?->full_name,
            'employee_code' => $overtime->employee?->employee_code,
            'date' => $overtime->date?->toDateString(),
            'hours' => $overtime->hours,
            'status' => $overtime->status,
            'approver_name' => $overtime->approver?->full_name,
        ];
    }
}
