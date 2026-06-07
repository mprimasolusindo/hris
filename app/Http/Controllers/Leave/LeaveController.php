<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\LeaveType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeaveController extends Controller
{
    public const TYPES = [
        'annual',
        'sick',
        'unpaid',
        'maternity',
        'paternity',
        'marriage',
        'bereavement',
        'other',
    ];

    /**
     * Resolve the active leave-type codes. Sourced from the lv_leave_types
     * master table when populated, falling back to the TYPES constant so the
     * module keeps working before any types are seeded.
     *
     * @return array<int, string>
     */
    public static function typeCodes(): array
    {
        $codes = LeaveType::query()->orderBy('name')->pluck('code')->all();

        return $codes !== [] ? $codes : self::TYPES;
    }

    public function index(Request $request): Response
    {
        $status = (string) $request->query('status', '');
        $type = (string) $request->query('type', '');

        $leaves = Leave::query()
            ->with('employee:id,full_name,employee_code')
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->when($type !== '', fn ($q) => $q->where('type', $type))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $leaves->getCollection()->transform(fn (Leave $leave) => $this->serializeLeave($leave));

        return Inertia::render('Leave/Index', [
            'leaves' => $leaves,
            'filters' => ['status' => $status, 'type' => $type],
            'typeOptions' => self::typeCodes(),
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
            'type' => ['required', 'string', 'in:'.implode(',', self::typeCodes())],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        Leave::query()->create($data + ['status' => 'pending']);

        return redirect()->route('leave.index')->with('success', 'Leave request submitted.');
    }

    public function cancel(Leave $leave): RedirectResponse
    {
        if ($leave->status === 'pending') {
            $leave->update(['status' => 'cancelled']);
        }

        return redirect()->route('leave.index')->with('success', 'Leave request cancelled.');
    }

    private function serializeLeave(Leave $leave): array
    {
        return [
            'id' => $leave->id,
            'employee_id' => $leave->employee_id,
            'employee_name' => $leave->employee?->full_name,
            'employee_code' => $leave->employee?->employee_code,
            'type' => $leave->type,
            'start_date' => $leave->start_date?->toDateString(),
            'end_date' => $leave->end_date?->toDateString(),
            'status' => $leave->status,
            'created_at' => $leave->created_at?->toDateTimeString(),
        ];
    }
}
