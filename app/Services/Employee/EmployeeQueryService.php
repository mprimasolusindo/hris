<?php

namespace App\Services\Employee;

use App\Models\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class EmployeeQueryService
{
    public function paginate(Request $request, int $perPage = 15): LengthAwarePaginator
    {
        $search = (string) $request->query('search', '');
        $status = (string) $request->query('status', '');

        return Employee::query()
            ->with([
                'company',
                'user:id,name,email',
                'jobs' => fn ($q) => $q->with(['department', 'position'])->orderByDesc('start_date'),
                'siteAssignments' => fn ($q) => $q->with('site')->orderByDesc('start_date'),
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('full_name', 'like', "%{$search}%")
                        ->orWhere('employee_code', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function loadForShow(Employee $employee): Employee
    {
        return $employee->load([
            'company',
            'user:id,name,email',
            'identity',
            'taxProfile',
            'familyMembers',
            'emergencyContacts',
            'bankAccounts',
            'allowances.component',
            'deductions.component',
            'loans',
            'documents.uploader:id,name',
            'jobs' => fn ($q) => $q->with(['department', 'position', 'manager'])->orderByDesc('start_date'),
            'siteAssignments' => fn ($q) => $q->with('site')->orderByDesc('start_date'),
            'contracts' => fn ($q) => $q->orderByDesc('start_date')->limit(10),
            'attendances' => fn ($q) => $q->orderByDesc('clock_in')->limit(20),
            'payrolls' => fn ($q) => $q->latest()->limit(6),
        ]);
    }
}
