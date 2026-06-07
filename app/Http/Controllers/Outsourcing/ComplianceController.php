<?php

namespace App\Http\Controllers\Outsourcing;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\EmploymentContract;
use App\Models\OutsourcingComplianceRecord;
use App\Models\VendorEmployee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ComplianceController extends Controller
{
    public function index(Request $request): Response
    {
        $vendorId = (string) $request->query('vendor_id', '');
        $severity = (string) $request->query('severity', '');

        $today = now()->startOfDay();
        $flags = [];

        $placements = VendorEmployee::query()
            ->with([
                'vendor:id,name',
                'employee:id,full_name,employee_code,status',
                'employee.contracts',
            ])
            ->when($vendorId !== '', fn ($q) => $q->where('vendor_id', $vendorId))
            ->get();

        foreach ($placements as $placement) {
            $employee = $placement->employee;
            if (! $employee) {
                continue;
            }

            $outsourcingContracts = $employee->contracts
                ->where('contract_type', 'outsourcing');

            $activeContract = $outsourcingContracts->first(function (EmploymentContract $contract) use ($today) {
                if ($contract->start_date && $contract->start_date->gt($today)) {
                    return false;
                }
                if ($contract->end_date && $contract->end_date->lt($today)) {
                    return false;
                }

                return true;
            });

            if (! $activeContract) {
                $flags[] = $this->makeFlag($placement, 'high', 'missing_outsourcing_contract', 'No active outsourcing employment contract.');
            } elseif ($activeContract->end_date && $activeContract->end_date->lte($today->copy()->addDays(30))) {
                $flags[] = $this->makeFlag($placement, 'medium', 'contract_expiring_soon', 'Outsourcing contract ends '.$activeContract->end_date->toDateString().'.');
            }

            if ($employee->status !== 'active') {
                $flags[] = $this->makeFlag($placement, 'low', 'inactive_employee', 'Employee status is '.$employee->status.'.');
            }
        }

        $resolvedKeys = OutsourcingComplianceRecord::query()
            ->where('status', 'resolved')
            ->get(['employee_id', 'vendor_id', 'flag_type'])
            ->map(fn (OutsourcingComplianceRecord $r) => $r->employee_id.'-'.$r->vendor_id.'-'.$r->flag_type)
            ->flip();

        $allFlags = collect($flags);
        $openFlags = $allFlags
            ->reject(fn ($flag) => $resolvedKeys->has($flag['employee_id'].'-'.$flag['vendor_id'].'-'.$flag['type']))
            ->when($severity !== '', fn ($c) => $c->where('severity', $severity))
            ->values();

        $resolved = OutsourcingComplianceRecord::query()
            ->where('status', 'resolved')
            ->with(['employee:id,full_name,employee_code', 'vendor:id,name', 'resolver:id,name'])
            ->latest('resolved_at')
            ->limit(50)
            ->get()
            ->map(fn (OutsourcingComplianceRecord $r) => [
                'id' => $r->id,
                'type' => $r->flag_type,
                'vendor_name' => $r->vendor?->name,
                'employee_name' => $r->employee?->full_name,
                'employee_code' => $r->employee?->employee_code,
                'detail' => $r->description,
                'resolved_by' => $r->resolver?->name,
                'resolved_at' => $r->resolved_at?->toDateTimeString(),
            ]);

        return Inertia::render('Outsourcing/Compliance/Index', [
            'flags' => $openFlags,
            'resolved' => $resolved,
            'filters' => ['vendor_id' => $vendorId, 'severity' => $severity],
            'vendors' => Company::query()->where('type', 'vendor')->orderBy('name')->get(['id', 'name']),
            'summary' => [
                'total' => $openFlags->count(),
                'high' => $openFlags->where('severity', 'high')->count(),
                'medium' => $openFlags->where('severity', 'medium')->count(),
                'low' => $openFlags->where('severity', 'low')->count(),
                'resolved' => $resolved->count(),
            ],
        ]);
    }

    public function resolve(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'employee_id' => ['required', 'exists:emp_employees,id'],
            'vendor_id' => ['required', 'exists:org_companies,id'],
            'flag_type' => ['required', 'string', 'max:64'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        OutsourcingComplianceRecord::query()->updateOrCreate(
            [
                'employee_id' => $data['employee_id'],
                'vendor_id' => $data['vendor_id'],
                'flag_type' => $data['flag_type'],
            ],
            [
                'description' => $data['description'] ?? 'Resolved by coordinator.',
                'status' => 'resolved',
                'resolved_at' => now(),
                'resolved_by' => $request->user()?->id,
            ],
        );

        return redirect()->route('outsourcing.compliance.index')->with('success', 'Compliance flag resolved.');
    }

    /**
     * @return array<string, mixed>
     */
    private function makeFlag(VendorEmployee $placement, string $severity, string $type, string $detail): array
    {
        $employee = $placement->employee;

        return [
            'id' => $type.'-'.$placement->id,
            'severity' => $severity,
            'type' => $type,
            'employee_id' => $placement->employee_id,
            'vendor_id' => $placement->vendor_id,
            'vendor_name' => $placement->vendor?->name,
            'employee_name' => $employee?->full_name,
            'employee_code' => $employee?->employee_code,
            'detail' => $detail,
        ];
    }
}
