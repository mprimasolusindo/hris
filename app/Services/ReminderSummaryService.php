<?php

namespace App\Services;

use App\Models\EmployeeShift;
use App\Models\EmploymentContract;
use App\Models\Leave;
use App\Models\OutsourcingComplianceRecord;
use App\Models\VendorEmployee;
use Illuminate\Support\Carbon;

class ReminderSummaryService
{
    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $today = now()->startOfDay();
        $in30 = $today->copy()->addDays(30);

        $pendingLeaves = Leave::query()
            ->with('employee:id,full_name')
            ->where('status', 'pending')
            ->orderBy('created_at')
            ->limit(5)
            ->get();

        $expiringContracts = EmploymentContract::query()
            ->with('employee:id,full_name')
            ->whereNotNull('end_date')
            ->whereDate('end_date', '>=', $today)
            ->whereDate('end_date', '<=', $in30)
            ->orderBy('end_date')
            ->limit(5)
            ->get()
            ->filter(function (EmploymentContract $contract) use ($today) {
                if ($contract->start_date && $contract->start_date->gt($today)) {
                    return false;
                }
                if ($contract->end_date && $contract->end_date->lt($today)) {
                    return false;
                }

                return true;
            });

        $pendingLeaveCount = Leave::query()->where('status', 'pending')->count();

        $expiringContractsCount = EmploymentContract::query()
            ->whereNotNull('end_date')
            ->whereDate('end_date', '>=', $today)
            ->whereDate('end_date', '<=', $in30)
            ->get()
            ->filter(fn (EmploymentContract $c) => ! $c->start_date?->gt($today))
            ->count();

        $todayShiftCount = EmployeeShift::query()
            ->whereDate('date', $today)
            ->count();

        $outsourcedHeadcount = VendorEmployee::query()
            ->whereHas('employee', fn ($q) => $q->where('status', 'active'))
            ->distinct('employee_id')
            ->count('employee_id');

        $openComplianceFlagsCount = $this->countComplianceFlags();

        return [
            'pendingLeaveCount' => $pendingLeaveCount,
            'expiringContractsCount' => $expiringContractsCount,
            'expiringVendorAgreementsCount' => 0,
            'openComplianceFlagsCount' => $openComplianceFlagsCount,
            'todayShiftCount' => $todayShiftCount,
            'outsourcedHeadcount' => $outsourcedHeadcount,
            'pendingLeaveRequests' => $pendingLeaves->map(fn (Leave $leave) => [
                'id' => (string) $leave->id,
                'employee_name' => $leave->employee?->full_name ?? '—',
                'start_date' => $leave->start_date?->toDateString(),
                'end_date' => $leave->end_date?->toDateString(),
                'created_at' => $leave->created_at?->toDateTimeString(),
            ])->values()->all(),
            'expiringContracts' => $expiringContracts->map(function (EmploymentContract $contract) use ($today) {
                return [
                    'id' => (string) $contract->id,
                    'contract_number' => (string) $contract->id,
                    'employee_name' => $contract->employee?->full_name ?? '—',
                    'days_left' => $contract->end_date
                        ? (int) $today->diffInDays($contract->end_date, false)
                        : 0,
                ];
            })->values()->all(),
            'expiringVendorAgreements' => [],
        ];
    }

    private function countComplianceFlags(): int
    {
        $today = now()->startOfDay();
        $count = 0;

        $resolvedKeys = OutsourcingComplianceRecord::query()
            ->where('status', 'resolved')
            ->get(['employee_id', 'vendor_id', 'flag_type'])
            ->map(fn (OutsourcingComplianceRecord $r) => $r->employee_id.'-'.$r->vendor_id.'-'.$r->flag_type)
            ->flip();

        $isResolved = fn (VendorEmployee $placement, string $type): bool => $resolvedKeys->has(
            $placement->employee_id.'-'.$placement->vendor_id.'-'.$type
        );

        $placements = VendorEmployee::query()
            ->with(['employee.contracts'])
            ->get();

        foreach ($placements as $placement) {
            $employee = $placement->employee;
            if (! $employee) {
                continue;
            }

            $activeContract = $employee->contracts
                ->where('contract_type', 'outsourcing')
                ->first(function (EmploymentContract $contract) use ($today) {
                    if ($contract->start_date && $contract->start_date->gt($today)) {
                        return false;
                    }
                    if ($contract->end_date && $contract->end_date->lt($today)) {
                        return false;
                    }

                    return true;
                });

            if (! $activeContract) {
                if (! $isResolved($placement, 'missing_outsourcing_contract')) {
                    $count++;
                }
            } elseif ($activeContract->end_date && $activeContract->end_date->lte($today->copy()->addDays(30))) {
                if (! $isResolved($placement, 'contract_expiring_soon')) {
                    $count++;
                }
            }

            if ($employee->status !== 'active' && ! $isResolved($placement, 'inactive_employee')) {
                $count++;
            }
        }

        return $count;
    }
}
