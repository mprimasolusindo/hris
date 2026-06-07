<?php

namespace Database\Seeders;

use App\Http\Controllers\Leave\LeaveController;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Role;
use App\Models\SalaryComponent;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Database\Seeders\Support\IndonesianDemoData;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Additive supplement for {@see HrisIndonesiaDemoSeeder}.
 *
 * Fills the demo tables that the base seeder leaves empty (allowances,
 * deductions, contracts, shifts, leaves, overtime, recruitment, vendors,
 * SaaS/billing, settings, and ESS user accounts) for the EXISTING demo
 * company "PT Mitra Sejahtera Indonesia (Demo)" and its ~500 employees.
 *
 * Every section is idempotent: it checks for marker data before inserting,
 * so the seeder can be re-run safely and partially-applied runs self-heal.
 *
 * Run: `php artisan db:seed --class=HrisIndonesiaDemoSupplementSeeder`
 * (also invoked automatically by HrisIndonesiaDemoSeeder).
 *
 * NEVER run `php artisan migrate:fresh` to "reset" before seeding — the dev
 * database holds real test data.
 */
class HrisIndonesiaDemoSupplementSeeder extends Seeder
{
    private const COMPANY_NAME = 'PT Mitra Sejahtera Indonesia (Demo)';

    public function run(): void
    {
        $company = Company::query()->where('name', self::COMPANY_NAME)->first();

        if ($company === null) {
            $this->command?->warn('Supplement: demo company not found — nothing to supplement.');

            return;
        }

        $faker = IndonesianDemoData::makeFaker();

        /** @var Collection<int, Employee> $employees */
        $employees = Employee::query()
            ->where('company_id', $company->id)
            ->where('employee_code', 'like', 'EMP-%')
            ->orderBy('id')
            ->get();

        if ($employees->isEmpty()) {
            $this->command?->warn('Supplement: no demo employees found — nothing to supplement.');

            return;
        }

        $components = $this->seedSalaryComponents();
        $this->seedAllowancesAndDeductions($employees, $components, $faker);
        $this->seedEmergencyContacts($employees, $faker);
        $this->seedDocuments($employees);
        $this->seedLoans($employees, $faker);
        $this->seedContracts($employees, $faker);
        $this->seedShifts($employees);
        $this->seedLeaves($employees, $faker);
        $this->seedOvertimes($employees, $faker);
        $this->seedRecruitment($company, $faker);
        $this->seedInterviews($company, $faker);
        $this->seedTalent($company, $employees, $faker);
        $this->seedVendors($company, $employees, $faker);
        $this->seedVendorInvoices($faker);
        $this->seedComplianceRecords($faker);
        $this->seedSaasBilling($faker);
        $this->seedSettings();
        $this->seedEssUsers($employees);

        $this->command?->info('Supplement: demo supplemental data seeded for '.self::COMPANY_NAME.'.');
    }

    /**
     * Catalog of reusable salary components (transport, meal, position
     * allowance earnings + a loan deduction). Idempotent on the unique code.
     *
     * @return array<string, SalaryComponent>
     */
    private function seedSalaryComponents(): array
    {
        $defs = [
            'transport' => ['code' => 'ALW-TRANSPORT', 'name' => 'Tunjangan Transport', 'type' => 'earning', 'default_value' => 500_000, 'is_taxable' => true],
            'meal' => ['code' => 'ALW-MEAL', 'name' => 'Tunjangan Makan', 'type' => 'earning', 'default_value' => 400_000, 'is_taxable' => true],
            'position' => ['code' => 'ALW-POSITION', 'name' => 'Tunjangan Jabatan', 'type' => 'earning', 'default_value' => 1_000_000, 'is_taxable' => true],
            'loan' => ['code' => 'DED-LOAN', 'name' => 'Potongan Pinjaman', 'type' => 'deduction', 'default_value' => 0, 'is_taxable' => false],
        ];

        $components = [];
        foreach ($defs as $key => $def) {
            $components[$key] = SalaryComponent::query()->firstOrCreate(
                ['code' => $def['code']],
                [
                    'name' => $def['name'],
                    'type' => $def['type'],
                    'calculation_method' => 'fixed',
                    'default_value' => $def['default_value'],
                    'is_taxable' => $def['is_taxable'],
                ]
            );
        }

        return $components;
    }

    /**
     * @param  Collection<int, Employee>  $employees
     * @param  array<string, SalaryComponent>  $components
     */
    private function seedAllowancesAndDeductions(Collection $employees, array $components, Generator $faker): void
    {
        $ids = $employees->pluck('id')->all();

        if (DB::table('emp_allowances')->whereIn('employee_id', $ids)->exists()) {
            return;
        }

        $now = now();
        $effectiveStart = Carbon::now()->startOfYear()->toDateString();

        $allowances = [];
        $deductions = [];

        foreach ($employees->values() as $index => $employee) {
            $allowances[] = [
                'employee_id' => $employee->id,
                'component_id' => $components['transport']->id,
                'name' => $components['transport']->name,
                'amount' => 500_000,
                'taxable' => true,
                'effective_start' => $effectiveStart,
                'effective_end' => null,
                'status' => 'active',
                'recurring' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $allowances[] = [
                'employee_id' => $employee->id,
                'component_id' => $components['meal']->id,
                'name' => $components['meal']->name,
                'amount' => 400_000,
                'taxable' => true,
                'effective_start' => $effectiveStart,
                'effective_end' => null,
                'status' => 'active',
                'recurring' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // Position allowance only for ~1/3 (supervisory roles).
            if ($index % 3 === 0) {
                $allowances[] = [
                    'employee_id' => $employee->id,
                    'component_id' => $components['position']->id,
                    'name' => $components['position']->name,
                    'amount' => $faker->randomElement([750_000, 1_000_000, 1_500_000, 2_500_000]),
                    'taxable' => true,
                    'effective_start' => $effectiveStart,
                    'effective_end' => null,
                    'status' => 'active',
                    'recurring' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // Loan deduction for ~1/5 of employees.
            if ($index % 5 === 0) {
                $deductions[] = [
                    'employee_id' => $employee->id,
                    'component_id' => $components['loan']->id,
                    'name' => $components['loan']->name,
                    'value' => $faker->randomElement([250_000, 500_000, 750_000, 1_000_000]),
                    'effective_start' => $effectiveStart,
                    'effective_end' => null,
                    'status' => 'active',
                    'recurring' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($allowances, 500) as $chunk) {
            DB::table('emp_allowances')->insert($chunk);
        }

        foreach (array_chunk($deductions, 500) as $chunk) {
            DB::table('emp_deductions')->insert($chunk);
        }
    }

    /**
     * @param  Collection<int, Employee>  $employees
     */
    private function seedEmergencyContacts(Collection $employees, Generator $faker): void
    {
        $ids = $employees->pluck('id')->all();

        if (DB::table('emp_emergency_contacts')->whereIn('employee_id', $ids)->exists()) {
            return;
        }

        $now = now();
        $rows = [];

        foreach ($employees->values() as $index => $employee) {
            $rows[] = [
                'employee_id' => $employee->id,
                'name' => $faker->name(),
                'relationship' => $faker->randomElement(['Pasangan', 'Orang Tua', 'Saudara', 'Anak', 'Teman']),
                'phone' => IndonesianDemoData::indonesianMobile($faker),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('emp_emergency_contacts')->insert($chunk);
        }
    }

    /**
     * @param  Collection<int, Employee>  $employees
     */
    private function seedDocuments(Collection $employees): void
    {
        $ids = $employees->pluck('id')->all();

        if (DB::table('emp_documents')->whereIn('employee_id', $ids)->exists()) {
            return;
        }

        $now = now();
        $rows = [];

        foreach ($employees as $employee) {
            $code = $employee->employee_code;

            $rows[] = [
                'employee_id' => $employee->id,
                'category' => 'ktp',
                'file_path' => "demo/documents/{$code}/ktp.pdf",
                'original_name' => "KTP-{$code}.pdf",
                'mime_type' => 'application/pdf',
                'size' => 245_000,
                'uploaded_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $rows[] = [
                'employee_id' => $employee->id,
                'category' => 'contract',
                'file_path' => "demo/documents/{$code}/contract.pdf",
                'original_name' => "Kontrak-{$code}.pdf",
                'mime_type' => 'application/pdf',
                'size' => 512_000,
                'uploaded_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('emp_documents')->insert($chunk);
        }
    }

    /**
     * ~10% of employees have an outstanding loan.
     *
     * @param  Collection<int, Employee>  $employees
     */
    private function seedLoans(Collection $employees, Generator $faker): void
    {
        $ids = $employees->pluck('id')->all();

        if (DB::table('emp_loans')->whereIn('employee_id', $ids)->exists()) {
            return;
        }

        $now = now();
        $rows = [];

        foreach ($employees->values() as $index => $employee) {
            if ($index % 10 !== 0) {
                continue;
            }

            $amount = $faker->randomElement([5_000_000, 10_000_000, 15_000_000, 20_000_000]);
            $monthly = (int) round($amount / $faker->randomElement([6, 10, 12, 18]));
            $remaining = (int) round($amount * $faker->randomFloat(2, 0.2, 0.9));

            $rows[] = [
                'employee_id' => $employee->id,
                'amount' => $amount,
                'remaining_amount' => $remaining,
                'monthly_deduction' => $monthly,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($rows !== []) {
            DB::table('emp_loans')->insert($rows);
        }
    }

    /**
     * One contract per employee — PKWT/PKWTT mix; a subset of PKWT contracts
     * expire within the next 30 days to exercise expiry reminders.
     *
     * @param  Collection<int, Employee>  $employees
     */
    private function seedContracts(Collection $employees, Generator $faker): void
    {
        $ids = $employees->pluck('id')->all();

        if (DB::table('emp_contracts')->whereIn('employee_id', $ids)->exists()) {
            return;
        }

        $now = now();
        $rows = [];

        foreach ($employees->values() as $index => $employee) {
            $isPkwtt = $index % 5 < 3; // ~60% permanent
            $start = Carbon::parse($employee->join_date ?? Carbon::now()->subYears(2));
            $salaryBase = 7_000_000 + ($employee->id % 160) * 25_000;

            if ($isPkwtt) {
                $type = 'pkwtt';
                $end = null;
            } else {
                $type = 'pkwt';
                // ~1 in 8 PKWT contracts expire within 30 days.
                if ($index % 8 === 0) {
                    $end = Carbon::now()->addDays($faker->numberBetween(3, 29))->toDateString();
                } else {
                    $end = Carbon::now()->addMonths($faker->numberBetween(4, 24))->toDateString();
                }
            }

            $rows[] = [
                'employee_id' => $employee->id,
                'contract_type' => $type,
                'start_date' => $start->toDateString(),
                'end_date' => $end,
                'salary_base' => $salaryBase,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('emp_contracts')->insert($chunk);
        }
    }

    /**
     * Three shift templates + per-employee weekday assignments for the
     * current month (rotating Pagi/Siang/Malam).
     *
     * @param  Collection<int, Employee>  $employees
     */
    private function seedShifts(Collection $employees): void
    {
        $shifts = collect([
            ['name' => 'Pagi', 'start_time' => '08:00:00', 'end_time' => '17:00:00'],
            ['name' => 'Siang', 'start_time' => '14:00:00', 'end_time' => '22:00:00'],
            ['name' => 'Malam', 'start_time' => '22:00:00', 'end_time' => '06:00:00'],
        ])->map(function (array $def) {
            return \App\Models\Shift::query()->firstOrCreate(
                ['name' => $def['name']],
                ['start_time' => $def['start_time'], 'end_time' => $def['end_time']]
            );
        });

        $shiftIds = $shifts->pluck('id')->values()->all();
        $ids = $employees->pluck('id')->all();

        if (DB::table('rel_employee_shifts')->whereIn('employee_id', $ids)->exists()) {
            return;
        }

        $period = CarbonPeriod::create(
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        );

        $now = now();
        $batch = [];

        foreach ($period as $dayIndex => $date) {
            /** @var Carbon $date */
            if ($date->isWeekend()) {
                continue;
            }

            foreach ($employees->values() as $empIndex => $employee) {
                $shiftId = $shiftIds[($empIndex + $dayIndex) % count($shiftIds)];

                $batch[] = [
                    'employee_id' => $employee->id,
                    'shift_id' => $shiftId,
                    'date' => $date->toDateString(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if (count($batch) >= 1000) {
                    DB::table('rel_employee_shifts')->insertOrIgnore($batch);
                    $batch = [];
                }
            }
        }

        if ($batch !== []) {
            DB::table('rel_employee_shifts')->insertOrIgnore($batch);
        }
    }

    /**
     * Leave requests for ~30% of employees with mixed statuses. Uses the
     * lv_leave_types catalog when present, otherwise the string types from
     * {@see LeaveController::TYPES}.
     *
     * @param  Collection<int, Employee>  $employees
     */
    private function seedLeaves(Collection $employees, Generator $faker): void
    {
        $ids = $employees->pluck('id')->all();

        if (DB::table('lv_leaves')->whereIn('employee_id', $ids)->exists()) {
            return;
        }

        $types = $this->resolveLeaveTypes();
        $statuses = ['pending', 'approved', 'rejected'];
        $now = now();
        $rows = [];
        $leaveCounter = 0;

        foreach ($employees->values() as $index => $employee) {
            if ($index % 3 !== 0) {
                continue;
            }

            $requestCount = $faker->numberBetween(1, 2);
            for ($n = 0; $n < $requestCount; $n++) {
                $start = Carbon::now()->subDays($faker->numberBetween(1, 50));
                $end = (clone $start)->addDays($faker->numberBetween(0, 4));

                $rows[] = [
                    'employee_id' => $employee->id,
                    'type' => $faker->randomElement($types),
                    'start_date' => $start->toDateString(),
                    'end_date' => $end->toDateString(),
                    'status' => $statuses[$leaveCounter % count($statuses)],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $leaveCounter++;
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('lv_leaves')->insert($chunk);
        }
    }

    /**
     * Seed (if needed) and return the list of leave type identifiers used on
     * lv_leaves.type. Falls back to controller string constants.
     *
     * @return list<string>
     */
    private function resolveLeaveTypes(): array
    {
        if (! Schema::hasTable('lv_leave_types')) {
            return LeaveController::TYPES;
        }

        $catalog = [
            ['code' => 'annual', 'name' => 'Cuti Tahunan', 'annual_entitlement_days' => 12, 'is_paid' => true],
            ['code' => 'sick', 'name' => 'Cuti Sakit', 'annual_entitlement_days' => 12, 'is_paid' => true],
            ['code' => 'unpaid', 'name' => 'Cuti Tanpa Gaji', 'annual_entitlement_days' => 0, 'is_paid' => false],
            ['code' => 'maternity', 'name' => 'Cuti Melahirkan', 'annual_entitlement_days' => 90, 'is_paid' => true],
            ['code' => 'paternity', 'name' => 'Cuti Ayah', 'annual_entitlement_days' => 2, 'is_paid' => true],
            ['code' => 'marriage', 'name' => 'Cuti Menikah', 'annual_entitlement_days' => 3, 'is_paid' => true],
            ['code' => 'bereavement', 'name' => 'Cuti Duka', 'annual_entitlement_days' => 2, 'is_paid' => true],
            ['code' => 'other', 'name' => 'Izin Lainnya', 'annual_entitlement_days' => 0, 'is_paid' => false],
        ];

        foreach ($catalog as $type) {
            \App\Models\LeaveType::query()->firstOrCreate(
                ['code' => $type['code']],
                [
                    'name' => $type['name'],
                    'annual_entitlement_days' => $type['annual_entitlement_days'],
                    'is_paid' => $type['is_paid'],
                ]
            );
        }

        return array_column($catalog, 'code');
    }

    /**
     * Overtime claims for ~5% of employees, with an approved subset.
     *
     * @param  Collection<int, Employee>  $employees
     */
    private function seedOvertimes(Collection $employees, Generator $faker): void
    {
        $ids = $employees->pluck('id')->all();

        if (DB::table('ot_overtimes')->whereIn('employee_id', $ids)->exists()) {
            return;
        }

        $approverId = $employees->first()->id;
        $now = now();
        $rows = [];

        foreach ($employees->values() as $index => $employee) {
            if ($index % 20 !== 0) {
                continue;
            }

            $approved = $index % 40 === 0;

            $rows[] = [
                'employee_id' => $employee->id,
                'date' => Carbon::now()->subDays($faker->numberBetween(1, 25))->toDateString(),
                'hours' => $faker->randomElement([1.5, 2, 2.5, 3, 4]),
                'approved_by' => $approved ? $approverId : null,
                'status' => $approved ? 'approved' : 'pending',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($rows !== []) {
            DB::table('ot_overtimes')->insert($rows);
        }
    }

    /**
     * Recruitment pipeline: 10 jobs, 30 candidates, 50 applications.
     */
    private function seedRecruitment(Company $company, Generator $faker): void
    {
        if (DB::table('trx_jobs')->where('company_id', $company->id)->exists()) {
            return;
        }

        $now = now();

        $jobTitles = array_slice(IndonesianDemoData::POSITION_NAMES, 0, 10);
        $jobRows = [];
        foreach ($jobTitles as $i => $title) {
            $jobRows[] = [
                'company_id' => $company->id,
                'title' => $title,
                'status' => $faker->randomElement(['open', 'open', 'on_hold', 'closed']),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('trx_jobs')->insert($jobRows);
        $jobIds = DB::table('trx_jobs')->where('company_id', $company->id)->pluck('id')->all();

        $candidateRows = [];
        for ($i = 1; $i <= 30; $i++) {
            $name = $faker->name();
            $candidateRows[] = [
                'name' => $name,
                'email' => sprintf('kandidat.%03d@demo-seed.hris.local', $i),
                'phone' => IndonesianDemoData::indonesianMobile($faker),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('trx_candidates')->insert($candidateRows);
        $candidateIds = DB::table('trx_candidates')
            ->where('email', 'like', 'kandidat.%@demo-seed.hris.local')
            ->pluck('id')
            ->all();

        $stages = ['applied', 'screening', 'interview', 'offer', 'hired', 'rejected'];
        $seenPairs = [];
        $applicationRows = [];
        $attempts = 0;
        while (count($applicationRows) < 50 && $attempts < 500) {
            $attempts++;
            $candidateId = $faker->randomElement($candidateIds);
            $jobId = $faker->randomElement($jobIds);
            $pair = $candidateId.'-'.$jobId;
            if (isset($seenPairs[$pair])) {
                continue;
            }
            $seenPairs[$pair] = true;

            $applicationRows[] = [
                'candidate_id' => $candidateId,
                'job_id' => $jobId,
                'stage' => $faker->randomElement($stages),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('trx_applications')->insertOrIgnore($applicationRows);
    }

    /**
     * Interviews for demo applications that have advanced to interview-or-later
     * stages. Idempotent on the demo company's application ids.
     */
    private function seedInterviews(Company $company, Generator $faker): void
    {
        if (! Schema::hasTable('trx_interviews')) {
            return;
        }

        $jobIds = DB::table('trx_jobs')->where('company_id', $company->id)->pluck('id')->all();

        if ($jobIds === []) {
            return;
        }

        $applicationIds = DB::table('trx_applications')
            ->whereIn('job_id', $jobIds)
            ->whereIn('stage', ['interview', 'offer', 'hired'])
            ->pluck('id')
            ->all();

        if ($applicationIds === []) {
            return;
        }

        if (DB::table('trx_interviews')->whereIn('application_id', $applicationIds)->exists()) {
            return;
        }

        $now = now();
        $statuses = ['scheduled', 'completed', 'completed', 'cancelled', 'no_show'];
        $rows = [];

        foreach ($applicationIds as $index => $applicationId) {
            $status = $statuses[$index % count($statuses)];
            $scheduledAt = $status === 'scheduled'
                ? Carbon::now()->addDays($faker->numberBetween(1, 14))->setTime(9 + ($index % 7), 0)
                : Carbon::now()->subDays($faker->numberBetween(1, 20))->setTime(9 + ($index % 7), 0);

            $completed = $status === 'completed';

            $rows[] = [
                'application_id' => $applicationId,
                'scheduled_at' => $scheduledAt,
                'interviewer_name' => $faker->name(),
                'location' => $faker->randomElement(['Ruang Meeting 1', 'Ruang HR', 'Google Meet', 'Zoom']),
                'status' => $status,
                'feedback' => $completed ? $faker->sentence(10) : null,
                'rating' => $completed ? $faker->numberBetween(2, 5) : null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('trx_interviews')->insert($rows);
    }

    /**
     * Talent & Growth demo data: performance reviews, trainings + enrollments,
     * talent pool, succession plans, and nine-box assessments. Each section is
     * idempotent on its own marker data.
     *
     * @param  Collection<int, Employee>  $employees
     */
    private function seedTalent(Company $company, Collection $employees, Generator $faker): void
    {
        if (! Schema::hasTable('tal_performance_reviews')) {
            return;
        }

        $year = (int) now()->year;
        $now = now();
        $ids = $employees->pluck('id')->all();

        // Performance reviews — current year, ~1 review per 3rd employee.
        if (! DB::table('tal_performance_reviews')->whereIn('employee_id', $ids)->exists()) {
            $statuses = ['draft', 'submitted', 'acknowledged', 'finalized'];
            $rows = [];
            foreach ($employees->values() as $index => $employee) {
                if ($index % 3 !== 0) {
                    continue;
                }
                $rows[] = [
                    'employee_id' => $employee->id,
                    'reviewer_id' => null,
                    'period_year' => $year,
                    'period_quarter' => $faker->numberBetween(1, 4),
                    'rating' => $faker->randomFloat(2, 2.5, 4.9),
                    'goals' => $faker->sentence(8),
                    'notes' => $faker->sentence(12),
                    'status' => $statuses[$index % count($statuses)],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            foreach (array_chunk($rows, 500) as $chunk) {
                DB::table('tal_performance_reviews')->insert($chunk);
            }
        }

        // Trainings + enrollments (marker: name suffix "(Demo)").
        if (! DB::table('tal_trainings')->where('name', 'like', '%(Demo)')->exists()) {
            $trainingDefs = [
                ['name' => 'K3 & Keselamatan Kerja (Demo)', 'status' => 'completed', 'offset' => -60],
                ['name' => 'Leadership Fundamentals (Demo)', 'status' => 'ongoing', 'offset' => -7],
                ['name' => 'Excel for HR (Demo)', 'status' => 'planned', 'offset' => 21],
                ['name' => 'Customer Service Excellence (Demo)', 'status' => 'planned', 'offset' => 45],
            ];

            foreach ($trainingDefs as $def) {
                $start = Carbon::now()->addDays($def['offset']);
                $trainingId = DB::table('tal_trainings')->insertGetId([
                    'name' => $def['name'],
                    'description' => $faker->sentence(14),
                    'start_date' => $start->toDateString(),
                    'end_date' => (clone $start)->addDays($faker->numberBetween(1, 4))->toDateString(),
                    'location' => $faker->randomElement(['Jakarta', 'Bandung', 'Online', 'Surabaya']),
                    'status' => $def['status'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $enrollStatus = $def['status'] === 'completed' ? 'completed' : 'registered';
                $enrollRows = [];
                foreach ($employees->random(min(15, $employees->count())) as $employee) {
                    $enrollRows[] = [
                        'training_id' => $trainingId,
                        'employee_id' => $employee->id,
                        'status' => $enrollStatus,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                DB::table('rel_training_employees')->insertOrIgnore($enrollRows);
            }
        }

        // Talent pool — ~1 in 8 employees flagged as high-potential.
        if (! DB::table('tal_talent_pool')->whereIn('employee_id', $ids)->exists()) {
            $readiness = ['ready_now', 'ready_1_2_years', 'ready_3_plus_years'];
            $potential = ['medium', 'high', 'high'];
            $rows = [];
            foreach ($employees->values() as $index => $employee) {
                if ($index % 8 !== 0) {
                    continue;
                }
                $rows[] = [
                    'employee_id' => $employee->id,
                    'readiness' => $readiness[$index % count($readiness)],
                    'potential' => $potential[$index % count($potential)],
                    'notes' => $faker->sentence(8),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            foreach (array_chunk($rows, 500) as $chunk) {
                DB::table('tal_talent_pool')->insert($chunk);
            }
        }

        // Succession plans — for the first few positions, with distinct successor/incumbent.
        if (! DB::table('tal_succession_plans')->exists()) {
            $positionIds = DB::table('org_positions')->orderBy('id')->limit(6)->pluck('id')->all();
            $readiness = ['ready_now', 'ready_1_2_years', 'ready_3_plus_years'];
            $pool = $employees->values();
            $rows = [];
            foreach ($positionIds as $index => $positionId) {
                $successor = $pool[($index * 2) % $pool->count()] ?? null;
                $incumbent = $pool[($index * 2 + 1) % $pool->count()] ?? null;
                if ($successor === null) {
                    continue;
                }
                $rows[] = [
                    'position_id' => $positionId,
                    'successor_id' => $successor->id,
                    'incumbent_id' => $incumbent?->id !== $successor->id ? $incumbent?->id : null,
                    'readiness' => $readiness[$index % count($readiness)],
                    'notes' => $faker->sentence(8),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            if ($rows !== []) {
                DB::table('tal_succession_plans')->insert($rows);
            }
        }

        // Nine-box assessments — current year, ~1 in 6 employees.
        if (! DB::table('tal_nine_box_assessments')->whereIn('employee_id', $ids)->exists()) {
            $rows = [];
            foreach ($employees->values() as $index => $employee) {
                if ($index % 6 !== 0) {
                    continue;
                }
                $performance = $faker->numberBetween(1, 3);
                $potentialScore = $faker->numberBetween(1, 3);
                $rows[] = [
                    'employee_id' => $employee->id,
                    'period_year' => $year,
                    'performance_score' => $performance,
                    'potential_score' => $potentialScore,
                    'box_label' => \App\Http\Controllers\Talent\NineBoxController::BOX_LABELS[$performance.'-'.$potentialScore] ?? null,
                    'notes' => $faker->sentence(8),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            foreach (array_chunk($rows, 500) as $chunk) {
                DB::table('tal_nine_box_assessments')->insert($chunk);
            }
        }
    }

    /**
     * Two outsourcing vendors + ~20 employee placements.
     *
     * @param  Collection<int, Employee>  $employees
     */
    private function seedVendors(Company $company, Collection $employees, Generator $faker): void
    {
        $vendorNames = [
            'PT Sigap Karya Mandiri (Vendor Demo)',
            'PT Andal Sumber Daya (Vendor Demo)',
        ];

        $vendors = collect($vendorNames)->map(function (string $name) {
            return Company::query()->firstOrCreate(
                ['tenant_id' => null, 'name' => $name],
                ['type' => 'vendor']
            );
        });

        $vendorIds = $vendors->pluck('id')->values()->all();
        $ids = $employees->pluck('id')->all();

        if (DB::table('rel_vendor_employees')->whereIn('employee_id', $ids)->exists()) {
            return;
        }

        $now = now();
        $rows = [];
        // Last 20 employees are deployed via vendors (alih daya / outsourcing).
        foreach ($employees->reverse()->take(20)->values() as $index => $employee) {
            $rows[] = [
                'employee_id' => $employee->id,
                'vendor_id' => $vendorIds[$index % count($vendorIds)],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('rel_vendor_employees')->insertOrIgnore($rows);
    }

    /**
     * Commercial vendor invoices for the demo vendors covering the last three
     * months (one issued + one paid per vendor). Idempotent on existing rows.
     */
    private function seedVendorInvoices(Generator $faker): void
    {
        $vendors = Company::query()
            ->where('type', 'vendor')
            ->where('name', 'like', '%(Vendor Demo)')
            ->get(['id']);

        if ($vendors->isEmpty()) {
            return;
        }

        $vendorIds = $vendors->pluck('id')->all();

        if (DB::table('bill_vendor_invoices')->whereIn('vendor_id', $vendorIds)->exists()) {
            return;
        }

        $now = now();
        $rows = [];

        foreach ($vendors as $vendor) {
            $headcount = DB::table('rel_vendor_employees')
                ->where('vendor_id', $vendor->id)
                ->count();
            $monthlyAmount = max($headcount, 1) * 5_000_000;

            for ($i = 2; $i >= 0; $i--) {
                $periodStart = Carbon::now()->subMonths($i)->startOfMonth();
                $periodEnd = (clone $periodStart)->endOfMonth();
                $paid = $i > 0;

                $rows[] = [
                    'vendor_id' => $vendor->id,
                    'tenant_id' => null,
                    'invoice_number' => sprintf('INV-%s-%03d', $periodStart->format('Ym'), $vendor->id),
                    'period_start' => $periodStart->toDateString(),
                    'period_end' => $periodEnd->toDateString(),
                    'amount' => $monthlyAmount,
                    'status' => $paid ? 'paid' : 'issued',
                    'paid_at' => $paid ? (clone $periodEnd)->addDays(5) : null,
                    'created_at' => $periodStart,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('bill_vendor_invoices')->insertOrIgnore($rows);
    }

    /**
     * A small set of resolved outsourcing compliance records, so the
     * compliance dashboard's "Resolved" section is populated in the demo.
     */
    private function seedComplianceRecords(Generator $faker): void
    {
        if (DB::table('outsourcing_compliance_records')->exists()) {
            return;
        }

        $placements = DB::table('rel_vendor_employees')
            ->orderBy('id')
            ->limit(3)
            ->get(['employee_id', 'vendor_id']);

        if ($placements->isEmpty()) {
            return;
        }

        $resolver = User::query()->orderBy('id')->value('id');
        $now = now();
        $rows = [];

        foreach ($placements as $placement) {
            $rows[] = [
                'employee_id' => $placement->employee_id,
                'vendor_id' => $placement->vendor_id,
                'flag_type' => 'missing_outsourcing_contract',
                'description' => 'Outsourcing contract uploaded and verified by coordinator.',
                'status' => 'resolved',
                'resolved_at' => $now->copy()->subDays($faker->numberBetween(1, 20)),
                'resolved_by' => $resolver,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('outsourcing_compliance_records')->insert($rows);
    }

    /**
     * SaaS primitives: tenant, plans, an active subscription, and billing
     * payment history.
     */
    private function seedSaasBilling(Generator $faker): void
    {
        $tenant = \App\Models\Tenant::query()->firstOrCreate(
            ['name' => 'Mitra Sejahtera Group (Demo)'],
            ['status' => 'active']
        );

        $planDefs = [
            ['name' => 'Starter', 'price' => 1_500_000, 'employee_limit' => 50],
            ['name' => 'Business', 'price' => 5_000_000, 'employee_limit' => 250],
            ['name' => 'Enterprise', 'price' => 12_000_000, 'employee_limit' => null],
        ];

        $plans = collect($planDefs)->map(function (array $def) {
            return \App\Models\SubscriptionPlan::query()->firstOrCreate(
                ['name' => $def['name']],
                ['price' => $def['price'], 'employee_limit' => $def['employee_limit']]
            );
        });

        $enterprise = $plans->firstWhere('name', 'Enterprise');

        if (! DB::table('sub_subscriptions')->where('tenant_id', $tenant->id)->exists()) {
            \App\Models\Subscription::query()->create([
                'tenant_id' => $tenant->id,
                'plan_id' => $enterprise->id,
                'start_date' => Carbon::now()->subMonths(6)->toDateString(),
                'end_date' => Carbon::now()->addMonths(6)->toDateString(),
                'status' => 'active',
            ]);
        }

        if (! DB::table('bill_payments')->where('tenant_id', $tenant->id)->exists()) {
            $now = now();
            $rows = [];
            for ($i = 5; $i >= 0; $i--) {
                $paidAt = Carbon::now()->subMonths($i)->startOfMonth()->addDays(3);
                $rows[] = [
                    'tenant_id' => $tenant->id,
                    'amount' => 12_000_000,
                    'method' => $faker->randomElement(['transfer', 'virtual_account', 'credit_card']),
                    'status' => $i === 0 ? 'pending' : 'paid',
                    'paid_at' => $i === 0 ? null : $paidAt,
                    'created_at' => $paidAt,
                    'updated_at' => $now,
                ];
            }
            DB::table('bill_payments')->insert($rows);
        }
    }

    private function seedSettings(): void
    {
        Setting::query()->firstOrCreate(
            ['key' => 'bug_report.enabled'],
            ['value' => '1']
        );
    }

    /**
     * Link the first ~50 employees to ESS user accounts (role: employee).
     *
     * @param  Collection<int, Employee>  $employees
     */
    private function seedEssUsers(Collection $employees): void
    {
        $ids = $employees->pluck('id')->all();

        if (DB::table('emp_employees')->whereIn('id', $ids)->whereNotNull('user_id')->exists()) {
            return;
        }

        $employeeRole = Role::query()->where('slug', 'employee')->first();

        foreach ($employees->take(50) as $employee) {
            $email = $employee->email ?: IndonesianDemoData::workEmail($employee->id);

            $user = User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => $employee->full_name,
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );

            if ($employeeRole !== null) {
                $user->roles()->syncWithoutDetaching([$employeeRole->id]);
            }

            $employee->forceFill(['user_id' => $user->id])->save();
        }
    }
}
