<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeaveBalanceController extends Controller
{
    public function index(Request $request): Response
    {
        $year = (int) $request->query('year', now()->year);

        $employees = Employee::query()
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'employee_code']);

        $approved = Leave::query()
            ->where('status', 'approved')
            ->whereYear('start_date', $year)
            ->get();

        $types = LeaveType::query()->orderBy('name')->get();

        // Map of type code => annual entitlement days from the master catalog.
        $entitlementByType = $types->pluck('annual_entitlement_days', 'code');
        $typeCodes = $types->isNotEmpty()
            ? $types->pluck('code')->all()
            : LeaveController::TYPES;

        $balances = $employees->map(function (Employee $employee) use ($approved, $year, $typeCodes, $entitlementByType) {
            $rows = $approved->where('employee_id', $employee->id);
            $byType = [];
            $totalUsed = 0;
            $totalEntitlement = 0;

            foreach ($typeCodes as $type) {
                $used = (int) $rows->where('type', $type)->sum(function (Leave $leave) {
                    if (! $leave->start_date || ! $leave->end_date) {
                        return 0;
                    }

                    return $leave->start_date->diffInDays($leave->end_date) + 1;
                });

                $entitlement = (int) ($entitlementByType[$type] ?? 0);

                if ($used === 0 && $entitlement === 0) {
                    continue;
                }

                $byType[$type] = [
                    'entitlement' => $entitlement,
                    'used' => $used,
                    'remaining' => $entitlement > 0 ? max(0, $entitlement - $used) : null,
                ];

                $totalUsed += $used;
                $totalEntitlement += $entitlement;
            }

            return [
                'employee_id' => $employee->id,
                'employee_name' => $employee->full_name,
                'employee_code' => $employee->employee_code,
                'year' => $year,
                'total_entitlement' => $totalEntitlement,
                'total_used' => $totalUsed,
                'total_remaining' => max(0, $totalEntitlement - $totalUsed),
                'by_type' => $byType,
            ];
        });

        return Inertia::render('Leave/Balance', [
            'year' => $year,
            'balances' => $balances,
            'typeOptions' => $typeCodes,
        ]);
    }
}
