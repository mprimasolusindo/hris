<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Support\ListPaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
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
        'permission',
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

    /**
     * True when the acting user may only submit/view their own leave records
     * (ESS). HR (types master) and managers (approvals) work company-wide.
     */
    public static function isSelfServiceOnly(): bool
    {
        $user = auth()->user();

        if ($user === null) {
            return true;
        }

        return ! $user->can('leave.approvals.view')
            && ! $user->can('leave.types.view');
    }

    /**
     * Linked employee id for the authenticated user, if any.
     */
    public static function currentEmployeeId(): ?int
    {
        $userId = auth()->id();

        if (! $userId) {
            return null;
        }

        return Employee::query()->where('user_id', $userId)->value('id');
    }

    public function index(Request $request): Response
    {
        $status = (string) $request->query('status', '');
        $type = (string) $request->query('type', '');
        $selfOnly = self::isSelfServiceOnly();
        $currentEmployeeId = self::currentEmployeeId();

        $perPage = ListPaginator::resolvePerPage($request);
        $leaves = ListPaginator::paginate(
            Leave::query()
                ->with('employee:id,full_name,employee_code')
                ->when($selfOnly, function ($q) use ($currentEmployeeId) {
                    if ($currentEmployeeId === null) {
                        $q->whereRaw('0 = 1');
                    } else {
                        $q->where('employee_id', $currentEmployeeId);
                    }
                })
                ->when($status !== '', fn ($q) => $q->where('status', $status))
                ->when($type !== '', fn ($q) => $q->where('type', $type))
                ->latest(),
            $request,
        );

        $leaves->getCollection()->transform(fn (Leave $leave) => $this->serializeLeave($leave));

        $employees = $selfOnly
            ? collect()
            : Employee::query()
                ->where('status', 'active')
                ->orderBy('full_name')
                ->get(['id', 'full_name', 'employee_code']);

        if ($selfOnly && $currentEmployeeId !== null) {
            $self = Employee::query()
                ->where('id', $currentEmployeeId)
                ->first(['id', 'full_name', 'employee_code']);
            if ($self) {
                $employees = collect([$self]);
            }
        }

        return Inertia::render('Leave/Index', [
            'leaves' => $leaves,
            'filters' => [
                'status' => $status,
                'type' => $type,
                'per_page' => is_string($perPage) ? $perPage : (string) $perPage,
            ],
            'typeOptions' => self::typeCodes(),
            'employees' => $employees->values(),
            'selfService' => $selfOnly,
            'linkedEmployee' => $currentEmployeeId !== null
                ? Employee::query()
                    ->where('id', $currentEmployeeId)
                    ->first(['id', 'full_name', 'employee_code'])
                : null,
            'canCreate' => auth()->user()?->can('leave.create') ?? false,
            'canCancel' => auth()->user()?->can('leave.cancel') ?? false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $selfOnly = self::isSelfServiceOnly();
        $currentEmployeeId = self::currentEmployeeId();

        if ($selfOnly) {
            if ($currentEmployeeId === null) {
                throw ValidationException::withMessages([
                    'employee_id' => 'Your login is not linked to an employee record. Contact HR.',
                ]);
            }
            $request->merge(['employee_id' => $currentEmployeeId]);
        }

        $data = $request->validate([
            'employee_id' => ['required', 'exists:emp_employees,id'],
            'type' => ['required', 'string', 'in:'.implode(',', self::typeCodes())],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($selfOnly && (int) $data['employee_id'] !== (int) $currentEmployeeId) {
            abort(403);
        }

        Leave::query()->create([
            'employee_id' => $data['employee_id'],
            'type' => $data['type'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'reason' => $data['reason'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()->route('leave.index')->with('success', 'Leave request submitted.');
    }

    public function cancel(Leave $leave): RedirectResponse
    {
        if ($leave->status !== 'pending') {
            return redirect()->route('leave.index')->with('error', 'Only pending leave can be cancelled.');
        }

        if (self::isSelfServiceOnly()) {
            $currentEmployeeId = self::currentEmployeeId();
            if ($currentEmployeeId === null || (int) $leave->employee_id !== (int) $currentEmployeeId) {
                abort(403);
            }
        }

        $leave->update(['status' => 'cancelled']);

        return redirect()->route('leave.index')->with('success', 'Leave request cancelled.');
    }

    /**
     * @return array{
     *     id: int,
     *     employee_id: int,
     *     employee_name: string|null,
     *     employee_code: string|null,
     *     type: string,
     *     start_date: string|null,
     *     end_date: string|null,
     *     reason: string|null,
     *     status: string,
     *     created_at: string|null
     * }
     */
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
            'reason' => $leave->reason,
            'status' => $leave->status,
            'created_at' => $leave->created_at?->toDateTimeString(),
        ];
    }
}
