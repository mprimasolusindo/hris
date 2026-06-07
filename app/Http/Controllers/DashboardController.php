<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\Payroll;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $today = now()->toDateString();
        $payrollThisMonth = Payroll::query()
            ->where('period_year', now()->year)
            ->where('period_month', now()->month)
            ->sum('net_salary');

        return Inertia::render('Dashboard', [
            'stats' => [
                'totalEmployees' => Employee::query()->count(),
                'activeEmployees' => Employee::query()->where('status', 'active')->count(),
                'attendanceToday' => Attendance::query()
                    ->whereDate('clock_in', $today)
                    ->count(),
                'payrollThisMonth' => (float) $payrollThisMonth,
            ],
            'chartData' => $this->chartData(),
            'recentActivity' => $this->recentActivity(),
        ]);
    }

    /**
     * Last 6 months of attendance counts (present/late) and payroll totals.
     *
     * @return array{attendance: array<int, array<string, mixed>>, payroll: array<int, array<string, mixed>>}
     */
    private function chartData(): array
    {
        $months = collect(range(5, 0))->map(fn (int $offset) => now()->startOfMonth()->subMonths($offset));

        $attendance = $months->map(function (Carbon $month) {
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();

            $records = Attendance::query()
                ->whereBetween('clock_in', [$start, $end])
                ->get(['status']);

            $late = $records->where('status', 'late')->count();

            return [
                'month' => $month->format('M'),
                'present' => $records->count() - $late,
                'late' => $late,
            ];
        })->values();

        $payroll = $months->map(function (Carbon $month) {
            $total = Payroll::query()
                ->where('period_year', $month->year)
                ->where('period_month', $month->month)
                ->sum('net_salary');

            return [
                'month' => $month->format('M'),
                // Convert to millions of IDR (Juta Rp) to match the chart label.
                'amount' => round(((float) $total) / 1_000_000, 1),
            ];
        })->values();

        return [
            'attendance' => $attendance->all(),
            'payroll' => $payroll->all(),
        ];
    }

    /**
     * Combined recent activity feed: latest leave requests, new hires, and
     * payroll runs, sorted newest-first.
     *
     * @return array<int, array<string, mixed>>
     */
    private function recentActivity(): array
    {
        $leaves = Leave::query()
            ->with('employee:id,full_name')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Leave $leave) => [
                'type' => 'leave',
                'title' => $leave->employee?->full_name ?? '—',
                'detail' => $leave->type,
                'status' => $leave->status,
                'timestamp' => $leave->created_at?->toIso8601String(),
            ]);

        $hires = Employee::query()
            ->whereNotNull('join_date')
            ->orderByDesc('join_date')
            ->limit(5)
            ->get(['id', 'full_name', 'employee_code', 'join_date'])
            ->map(fn (Employee $employee) => [
                'type' => 'hire',
                'title' => $employee->full_name,
                'detail' => $employee->employee_code,
                'status' => null,
                'timestamp' => $employee->join_date?->toIso8601String(),
            ]);

        $payrolls = Payroll::query()
            ->with('employee:id,full_name')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Payroll $payroll) => [
                'type' => 'payroll',
                'title' => $payroll->employee?->full_name ?? '—',
                'detail' => sprintf('%02d/%d', $payroll->period_month, $payroll->period_year),
                'status' => $payroll->status,
                'timestamp' => $payroll->created_at?->toIso8601String(),
            ]);

        return $leaves
            ->concat($hires)
            ->concat($payrolls)
            ->sortByDesc('timestamp')
            ->values()
            ->take(8)
            ->all();
    }
}
