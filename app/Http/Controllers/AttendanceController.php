<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceController extends Controller
{
    public function index(Request $request): Response
    {
        $date = (string) $request->query('date', now()->toDateString());
        $siteId = $request->query('site_id');
        $employeeId = $request->query('employee_id');

        $attendances = Attendance::query()
            ->with(['employee', 'site'])
            ->whereDate('clock_in', $date)
            ->when($siteId, fn ($query) => $query->where('site_id', $siteId))
            ->when($employeeId, fn ($query) => $query->where('employee_id', $employeeId))
            ->orderBy('clock_in')
            ->paginate(20)
            ->withQueryString();

        $collection = $attendances->getCollection();

        $summary = [
            'present' => $collection->where('status', 'present')->count(),
            'late' => $collection->where('status', 'late')->count(),
            'absent' => $collection->where('status', 'absent')->count(),
        ];

        $attendances->getCollection()->transform(function (Attendance $row) {
            return [
                'id' => $row->id,
                'employee_name' => $row->employee?->full_name,
                'employee_code' => $row->employee?->employee_code,
                'site_id' => $row->site_id,
                'site_name' => $row->site?->name,
                'clock_in' => $row->clock_in?->toDateTimeString(),
                'clock_out' => $row->clock_out?->toDateTimeString(),
                'status' => $row->status,
            ];
        });

        return Inertia::render('Attendance/Index', [
            'attendances' => $attendances,
            'filters' => [
                'date' => $date,
                'site_id' => $siteId ? (string) $siteId : '',
                'employee_id' => $employeeId ? (string) $employeeId : '',
            ],
            'summary' => $summary,
            'employees' => Employee::query()->orderBy('full_name')->get(['id', 'full_name', 'employee_code']),
            'sites' => Site::query()->orderBy('name')->get(['id', 'name']),
            'statusOptions' => ['present', 'late', 'absent', 'leave', 'sick', 'holiday'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'employee_id' => ['required', 'exists:emp_employees,id'],
            'site_id' => ['nullable', 'exists:org_sites,id'],
            'clock_in' => ['required', 'date'],
            'clock_out' => ['nullable', 'date', 'after_or_equal:clock_in'],
            'status' => ['required', 'string', 'max:32'],
        ]);

        Attendance::query()->create($data);

        return redirect()
            ->route('attendance.index')
            ->with('success', 'Attendance recorded.');
    }

    public function update(Request $request, Attendance $attendance): RedirectResponse
    {
        $data = $request->validate([
            'site_id' => ['nullable', 'exists:org_sites,id'],
            'clock_in' => ['required', 'date'],
            'clock_out' => ['nullable', 'date', 'after_or_equal:clock_in'],
            'status' => ['required', 'string', 'max:32'],
        ]);

        $attendance->update($data);

        return back()->with('success', 'Attendance updated.');
    }

    public function destroy(Attendance $attendance): RedirectResponse
    {
        $attendance->delete();

        return back()->with('success', 'Attendance removed.');
    }
}
