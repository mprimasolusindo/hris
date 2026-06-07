<?php

namespace App\Http\Controllers\Outsourcing;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Company;
use App\Models\Site;
use App\Models\VendorEmployee;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class PlacementTrackingController extends Controller
{
    public function index(Request $request): Response
    {
        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);
        $vendorId = (string) $request->query('vendor_id', '');
        $siteId = (string) $request->query('site_id', '');

        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();

        $placements = VendorEmployee::query()
            ->with([
                'vendor:id,name',
                'employee:id,full_name,employee_code,status',
            ])
            ->when($vendorId !== '', fn ($q) => $q->where('vendor_id', $vendorId))
            ->get();

        $employeeIds = $placements->pluck('employee_id')->unique()->values();

        $attendanceStats = Attendance::query()
            ->whereIn('employee_id', $employeeIds)
            ->whereBetween('clock_in', [$periodStart, $periodEnd])
            ->when($siteId !== '', fn ($q) => $q->where('site_id', $siteId))
            ->get()
            ->groupBy('employee_id')
            ->map(fn ($rows) => [
                'present_days' => $rows->whereIn('status', ['present', 'late'])->count(),
                'total_records' => $rows->count(),
            ]);

        $rows = $placements
            ->filter(fn (VendorEmployee $p) => $p->employee?->status === 'active')
            ->map(function (VendorEmployee $p) use ($attendanceStats) {
                $stats = $attendanceStats->get($p->employee_id, ['present_days' => 0, 'total_records' => 0]);

                return [
                    'id' => $p->id,
                    'vendor_name' => $p->vendor?->name,
                    'employee_name' => $p->employee?->full_name,
                    'employee_code' => $p->employee?->employee_code,
                    'present_days' => $stats['present_days'],
                    'attendance_records' => $stats['total_records'],
                ];
            })
            ->values();

        return Inertia::render('Outsourcing/Tracking/Index', [
            'rows' => $rows,
            'filters' => [
                'month' => $month,
                'year' => $year,
                'vendor_id' => $vendorId,
                'site_id' => $siteId,
            ],
            'vendors' => Company::query()->where('type', 'vendor')->orderBy('name')->get(['id', 'name']),
            'sites' => Site::query()->orderBy('name')->get(['id', 'name']),
            'summary' => [
                'active_placements' => $rows->count(),
                'total_present_days' => $rows->sum('present_days'),
            ],
        ]);
    }
}
