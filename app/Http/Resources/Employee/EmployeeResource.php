<?php

namespace App\Http\Resources\Employee;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Employee */
class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $currentJob = $this->jobs->first();

        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'company_id' => $this->company_id,
            'user_id' => $this->user_id,
            'employee_code' => $this->employee_code,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'gender' => $this->gender,
            'birth_date' => $this->birth_date?->toDateString(),
            'marital_status' => $this->marital_status,
            'religion' => $this->religion,
            'status' => $this->status,
            'join_date' => $this->join_date?->toDateString(),
            'resign_date' => $this->resign_date?->toDateString(),
            'profile_photo_url' => $this->profile_photo_path
                ? asset('storage/'.$this->profile_photo_path)
                : null,
            'company_name' => $this->company?->name,
            'site_name' => $this->siteAssignments->first()?->site?->name,
            'department_name' => $currentJob?->department?->name,
            'position_name' => $currentJob?->position?->name,
            'manager_name' => $currentJob?->manager?->full_name,
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
            ]),
            'identity' => $this->whenLoaded('identity', fn () => $this->identity ? [
                'id' => $this->identity->id,
                'nik' => $this->identity->nik,
                'npwp' => $this->identity->npwp,
                'bpjs_health' => $this->identity->bpjs_health,
                'bpjs_employment' => $this->identity->bpjs_employment,
                'address' => $this->identity->address,
                'city' => $this->identity->city,
            ] : null),
            'tax_profile' => $this->whenLoaded('taxProfile', fn () => $this->taxProfile ? [
                'id' => $this->taxProfile->id,
                'has_npwp' => $this->taxProfile->has_npwp,
                'npwp' => $this->taxProfile->npwp,
                'tax_status' => $this->taxProfile->tax_status,
                'tax_method' => $this->taxProfile->tax_method,
                'dependents_count' => $this->taxProfile->dependents_count,
            ] : null),
            'family_members' => $this->whenLoaded(
                'familyMembers',
                fn () => EmployeeFamilyMemberResource::collection($this->familyMembers)->resolve(),
            ),
            'emergency_contacts' => $this->whenLoaded(
                'emergencyContacts',
                fn () => EmployeeEmergencyContactResource::collection($this->emergencyContacts)->resolve(),
            ),
            'bank_accounts' => $this->whenLoaded(
                'bankAccounts',
                fn () => EmployeeBankAccountResource::collection($this->bankAccounts)->resolve(),
            ),
            'allowances' => $this->whenLoaded(
                'allowances',
                fn () => EmployeeAllowanceResource::collection($this->allowances)->resolve(),
            ),
            'deductions' => $this->whenLoaded(
                'deductions',
                fn () => EmployeeDeductionResource::collection($this->deductions)->resolve(),
            ),
            'documents' => $this->whenLoaded(
                'documents',
                fn () => EmployeeDocumentResource::collection($this->documents)->resolve(),
            ),
            'contracts' => $this->whenLoaded('contracts', fn () => $this->contracts->map(fn ($c) => [
                'id' => $c->id,
                'contract_type' => $c->contract_type,
                'start_date' => $c->start_date?->toDateString(),
                'end_date' => $c->end_date?->toDateString(),
                'salary_base' => $c->salary_base,
            ])),
            'loans' => $this->whenLoaded('loans', fn () => $this->loans->map(fn ($l) => [
                'id' => $l->id,
                'amount' => $l->amount,
                'remaining_amount' => $l->remaining_amount,
                'monthly_deduction' => $l->monthly_deduction,
            ])),
            'jobs' => $this->whenLoaded('jobs', fn () => $this->jobs->map(fn ($j) => [
                'id' => $j->id,
                'company_id' => $j->company_id,
                'company_name' => $j->company?->name,
                'department_id' => $j->department_id,
                'department_name' => $j->department?->name,
                'position_id' => $j->position_id,
                'position_name' => $j->position?->name,
                'manager_id' => $j->manager_id,
                'manager_name' => $j->manager?->full_name,
                'employment_type' => $j->employment_type,
                'start_date' => $j->start_date?->toDateString(),
                'end_date' => $j->end_date?->toDateString(),
            ])),
            'site_assignments' => $this->whenLoaded('siteAssignments', fn () => $this->siteAssignments->map(fn ($s) => [
                'id' => $s->id,
                'site_id' => $s->site_id,
                'site_name' => $s->site?->name,
                'start_date' => $s->start_date?->toDateString(),
                'end_date' => $s->end_date?->toDateString(),
            ])),
            'recent_payrolls' => $this->whenLoaded('payrolls', fn () => $this->payrolls->map(fn ($p) => [
                'id' => $p->id,
                'period_month' => $p->period_month,
                'period_year' => $p->period_year,
                'net_salary' => $p->net_salary,
                'status' => $p->status,
            ])),
            'recent_attendances' => $this->whenLoaded('attendances', fn () => $this->attendances->map(fn (Attendance $a) => [
                'id' => $a->id,
                'clock_in' => $a->clock_in?->toDateTimeString(),
                'clock_out' => $a->clock_out?->toDateTimeString(),
                'status' => $a->status,
            ])),
        ];
    }
}
