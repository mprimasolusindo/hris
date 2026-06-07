<?php

namespace App\Services\Payroll;

use App\Models\Attendance;
use App\Models\BpjsConfig;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\TaxRule;
use App\Support\Payroll\TerCategoryResolver;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Indonesia-compliance-grade monthly payroll calculator.
 *
 * Regulatory basis (all configurable rates live in cfg_bpjs / cfg_tax_rules,
 * never hard-coded here — see .cursor/rules/20-hr-research-indonesia.mdc):
 *   - BPJS Kesehatan      : Perpres 64/2020 (employee 1% of upah)
 *   - JHT / JP            : PP 46/2015 / PP 45/2015 (employee 2% / 1%)
 *   - JKK / JKM / JKP     : PP 44/2015 / PP 37/2021 (employer-borne, 0% employee)
 *   - PPh21 (TER monthly) : PMK 168/2023, effective 1 Jan 2024
 *       no-NPWP surcharge : +20% per UU 7/1983 jo. UU 7/2021 (HPP) ps. 21 ayat 5a
 *   - THR pro-rata        : Permenaker 6/2016 ps. 2–3
 *
 * The attendance allowance (present/late days × Rp 25.000) is a company
 * policy line, not a statutory figure.
 */
class PayrollCalculationService
{
    private const ATTENDANCE_DAILY_ALLOWANCE = 25000;

    private const NO_NPWP_SURCHARGE = 1.20;

    /**
     * Generate (or regenerate) a payroll for one employee and period and
     * write a detailed pay_payroll_items breakdown.
     */
    public function generate(Employee $employee, int $month, int $year, float $baseSalary): Payroll
    {
        $earnings = [];
        $deductions = [];

        // --- Earnings -------------------------------------------------------
        $earnings[] = ['component_name' => 'Base Salary', 'amount' => $baseSalary];

        $attendanceCount = Attendance::query()
            ->where('employee_id', $employee->id)
            ->whereYear('clock_in', $year)
            ->whereMonth('clock_in', $month)
            ->whereIn('status', ['present', 'late'])
            ->count();

        $attendanceAllowance = $attendanceCount * self::ATTENDANCE_DAILY_ALLOWANCE;
        if ($attendanceAllowance > 0) {
            $earnings[] = [
                'component_name' => 'Attendance Allowance',
                'amount' => (float) $attendanceAllowance,
            ];
        }

        // Recurring tunjangan (emp_allowances, active only).
        $employee->loadMissing(['allowances', 'deductions', 'loans', 'taxProfile']);

        foreach ($employee->allowances->where('status', 'active') as $allowance) {
            $earnings[] = [
                'component_name' => $allowance->name,
                'amount' => (float) $allowance->amount,
            ];
        }

        // THR — pro-rata religious-holiday allowance, paid in June here.
        $thr = $this->thrEarning($employee, $month, $year, $baseSalary);
        if ($thr !== null) {
            $earnings[] = $thr;
        }

        $grossSalary = array_sum(array_column($earnings, 'amount'));

        // --- Statutory deductions ------------------------------------------
        // BPJS — each program computed separately on the upah base (base salary).
        foreach (BpjsConfig::query()->orderBy('type')->get() as $bpjs) {
            $employeeRate = (float) $bpjs->employee_percentage;
            if ($employeeRate <= 0) {
                continue; // JKK / JKM / JKP employee share is 0 — borne by employer.
            }

            $deductions[] = [
                'component_name' => 'BPJS '.strtoupper($bpjs->type).' (Employee)',
                'amount' => round($baseSalary * $employeeRate, 2),
            ];
        }

        // PPh21 via the monthly TER table (PMK 168/2023).
        $pph21 = $this->pph21Deduction($employee, $grossSalary);
        if ($pph21 !== null) {
            $deductions[] = $pph21;
        }

        // --- Voluntary / contractual deductions ----------------------------
        foreach ($employee->deductions->where('status', 'active') as $deduction) {
            $amount = (float) $deduction->value;
            if ($amount <= 0) {
                continue;
            }

            $deductions[] = [
                'component_name' => $deduction->name,
                'amount' => round($amount, 2),
            ];
        }

        // Active loan repayments (emp_loans with remaining balance).
        foreach ($employee->loans->where('remaining_amount', '>', 0) as $loan) {
            $monthly = (float) $loan->monthly_deduction;
            if ($monthly <= 0) {
                continue;
            }

            $deductions[] = [
                'component_name' => 'Loan Repayment',
                'amount' => round(min($monthly, (float) $loan->remaining_amount), 2),
            ];
        }

        $totalDeduction = array_sum(array_column($deductions, 'amount'));
        $netSalary = $grossSalary - $totalDeduction;

        return DB::transaction(function () use (
            $employee,
            $month,
            $year,
            $grossSalary,
            $totalDeduction,
            $netSalary,
            $earnings,
            $deductions
        ): Payroll {
            $payroll = Payroll::query()->updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'period_month' => $month,
                    'period_year' => $year,
                ],
                [
                    'gross_salary' => $grossSalary,
                    'total_deduction' => $totalDeduction,
                    'net_salary' => $netSalary,
                    'status' => 'generated',
                    'approval_notes' => null,
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                    'approved_by' => null,
                    'approved_at' => null,
                    'paid_by' => null,
                    'paid_at' => null,
                ]
            );

            $payroll->items()->delete();

            $rows = [];
            foreach ($earnings as $earning) {
                $rows[] = [
                    'component_name' => $earning['component_name'],
                    'type' => 'earning',
                    'amount' => $earning['amount'],
                ];
            }
            foreach ($deductions as $deduction) {
                $rows[] = [
                    'component_name' => $deduction['component_name'],
                    'type' => 'deduction',
                    'amount' => $deduction['amount'],
                ];
            }

            $payroll->items()->createMany($rows);

            return $payroll->load(['employee', 'items']);
        });
    }

    /**
     * Pro-rata THR earning line, only in June (month 6).
     *
     * Permenaker 6/2016 ps. 2–3: ≥ 12 months service → 1× monthly wage;
     * 1–12 months → pro-rata (months_worked / 12). Employees who joined in
     * a prior year therefore receive a full month; this-year joiners are
     * pro-rated by months worked so far this calendar year.
     *
     * @return array{component_name: string, amount: float}|null
     */
    private function thrEarning(Employee $employee, int $month, int $year, float $baseSalary): ?array
    {
        if ($month !== 6 || $employee->join_date === null) {
            return null;
        }

        $joinDate = Carbon::parse($employee->join_date);
        $periodEnd = Carbon::create($year, $month, 1)->endOfMonth();

        if ($joinDate->greaterThan($periodEnd)) {
            return null;
        }

        if ($joinDate->year < $year) {
            $monthsWorked = 12; // ≥ 12 months service → full month wage.
        } else {
            // Joined this year: calendar months worked so far (join month inclusive).
            $monthsWorked = ($month - $joinDate->month) + 1;
            $monthsWorked = max(1, min(12, $monthsWorked));
        }

        $amount = round($baseSalary * ($monthsWorked / 12), 2);
        if ($amount <= 0) {
            return null;
        }

        return ['component_name' => 'THR (Tunjangan Hari Raya)', 'amount' => $amount];
    }

    /**
     * PPh21 monthly withholding via the TER table (PMK 168/2023). Maps the
     * employee PTKP status to a TER category, looks up the bracket rate for
     * the gross income band, then applies the 20% no-NPWP surcharge.
     *
     * @return array{component_name: string, amount: float}|null
     */
    private function pph21Deduction(Employee $employee, float $grossSalary): ?array
    {
        $profile = $employee->taxProfile;
        $category = TerCategoryResolver::resolve($profile?->tax_status);

        $rate = (float) TaxRule::query()
            ->where('rule_type', 'ter_monthly')
            ->where('ptkp_category', $category)
            ->where('gross_min', '<=', $grossSalary)
            ->where(function ($query) use ($grossSalary) {
                $query->whereNull('gross_max')
                    ->orWhere('gross_max', '>', $grossSalary);
            })
            ->orderByDesc('gross_min')
            ->value('value');

        if ($rate <= 0) {
            return null;
        }

        $tax = $grossSalary * $rate;

        $hasNpwp = $profile?->has_npwp ?? false;
        if (! $hasNpwp) {
            $tax *= self::NO_NPWP_SURCHARGE;
        }

        $tax = round($tax, 2);
        if ($tax <= 0) {
            return null;
        }

        $label = sprintf('PPh21 TER %s', $category);
        if (! $hasNpwp) {
            $label .= ' (+20% non-NPWP)';
        }

        return ['component_name' => $label, 'amount' => $tax];
    }
}
