<?php

namespace Database\Seeders;

use App\Models\BpjsConfig;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeJob;
use App\Models\EmployeeSite;
use App\Models\Position;
use App\Models\Site;
use App\Models\TaxRule;
use App\Services\Payroll\PayrollCalculationService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Database\Seeders\Support\IndonesianDemoData;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Large Indonesian HRIS demo dataset for local / QA environments.
 *
 * - 10 departments (org_departments)
 * - 500 employees (emp_employees) with unique demo emails
 * - Each employee has one active job (emp_jobs) linking to a department
 * - ~3 months of weekday attendance (att_attendances) with realistic statuses
 * - Payroll (pay_payrolls + pay_payroll_items) via {@see PayrollCalculationService}
 *   so gross/net align with attendance the same way as the web UI.
 *
 * Run: `php artisan db:seed --class=HrisIndonesiaDemoSeeder`
 *
 * Ensures {@see DemoAdminUserSeeder} runs first (login: admin@demo.hris.local / password).
 * Re-run safe: skips if employee `EMP-00001` already exists for the seeded company.
 * NEVER run `php artisan migrate:fresh` to "reset" before seeding — the dev
 * database holds real test data. This seeder is idempotent; just re-run it.
 */
class HrisIndonesiaDemoSeeder extends Seeder
{
    private const EMPLOYEE_COUNT = 500;

    public function run(): void
    {
        $this->call(DemoAdminUserSeeder::class);

        $faker = IndonesianDemoData::makeFaker();

        // Always (re)assert payroll configuration — cheap and idempotent.
        $this->seedPayrollConfiguration();

        $company = Company::query()->firstOrCreate(
            ['name' => 'PT Mitra Sejahtera Indonesia (Demo)'],
            ['tenant_id' => null, 'type' => 'main']
        );

        $employeesExist = Employee::query()
            ->where('company_id', $company->id)
            ->where('employee_code', 'EMP-00001')
            ->exists();

        if ($employeesExist) {
            $this->command?->info('HrisIndonesiaDemoSeeder: demo employees already exist — skipping employee creation, running supplemental seeding only.');
        } else {
            $this->seedCoreDemo($company, $faker);
        }

        // Additively fill the remaining demo tables (idempotent per-section).
        $this->call(HrisIndonesiaDemoSupplementSeeder::class);
    }

    /**
     * Full base dataset (employees + jobs/sites + profile extras + attendance
     * + payroll). Only runs when the demo employees do not yet exist.
     */
    private function seedCoreDemo(Company $company, Generator $faker): void
    {
        $site = Site::query()->firstOrCreate(
            [
                'company_id' => $company->id,
                'name' => 'Kantor Pusat Jakarta',
            ],
            ['location' => 'Jl. Sudirman, Jakarta Pusat']
        );

        $departments = collect(IndonesianDemoData::DEPARTMENT_NAMES)->map(function (string $name) use ($company) {
            return Department::query()->firstOrCreate(
                ['company_id' => $company->id, 'name' => $name],
                []
            );
        });

        $positions = collect(IndonesianDemoData::POSITION_NAMES)->map(function (string $name) {
            return Position::query()->firstOrCreate(
                ['name' => $name],
                []
            );
        });

        $this->seedEmployees($company->id, $faker);
        $employees = Employee::query()
            ->where('company_id', $company->id)
            ->where('employee_code', 'like', 'EMP-%')
            ->orderBy('employee_code')
            ->get();

        $deptIds = $departments->pluck('id')->values()->all();
        $posIds = $positions->pluck('id')->values()->all();
        $joinBase = Carbon::now()->subYears(3)->startOfMonth();

        foreach ($employees as $index => $employee) {
            $deptId = $deptIds[$index % count($deptIds)];
            $posId = $posIds[$index % count($posIds)];

            EmployeeJob::query()->create([
                'employee_id' => $employee->id,
                'company_id' => $company->id,
                'department_id' => $deptId,
                'position_id' => $posId,
                'manager_id' => null,
                'employment_type' => $faker->randomElement(['pkwtt', 'pkwt']),
                'start_date' => (clone $joinBase)->addDays($index % 400),
                'end_date' => null,
            ]);

            EmployeeSite::query()->create([
                'employee_id' => $employee->id,
                'site_id' => $site->id,
                'start_date' => (clone $joinBase)->addDays($index % 400),
                'end_date' => null,
            ]);

            $this->seedEmployeeProfileExtras($employee, $index, $faker);
        }

        $this->seedAttendanceForLastThreeMonths($employees, $site->id, $faker);

        $this->seedPayrollsForLastThreeMonths($employees);
    }

    private function seedEmployeeProfileExtras(Employee $employee, int $index, Generator $faker): void
    {
        \App\Models\EmployeeIdentity::query()->create([
            'employee_id' => $employee->id,
            'nik' => sprintf('3201%012d', ($index % 999999999999) + 100000000000),
            'npwp' => null,
            'bpjs_health' => sprintf('%013d', 1000000000000 + $employee->id),
            'bpjs_employment' => sprintf('%013d', 2000000000000 + $employee->id),
            'address' => $faker->streetAddress(),
            'city' => 'Jakarta',
        ]);

        \App\Models\EmployeeFamilyMember::query()->create([
            'employee_id' => $employee->id,
            'name' => $faker->name(),
            'relationship' => $index % 3 === 0 ? 'spouse' : 'child',
            'birth_date' => $faker->date(),
            'is_dependent' => true,
        ]);

        \App\Models\EmployeeBankAccount::query()->create([
            'employee_id' => $employee->id,
            'bank_name' => $faker->randomElement(['BCA', 'Mandiri', 'BNI', 'BRI']),
            'account_number' => $faker->numerify('##########'),
            'account_holder' => $employee->full_name,
            'is_primary' => true,
        ]);

        \App\Models\EmployeeTaxProfile::query()->create([
            'employee_id' => $employee->id,
            'has_npwp' => $index % 4 !== 0,
            'npwp' => null,
            'tax_status' => $faker->randomElement(['TK/0', 'TK/1', 'K/0', 'K/1']),
            'tax_method' => 'ter_monthly',
            'dependents_count' => $index % 4,
        ]);
    }

    private function seedPayrollConfiguration(): void
    {
        BpjsConfig::query()->updateOrCreate(
            ['type' => 'kesehatan'],
            ['employee_percentage' => '0.0100', 'company_percentage' => '0.0400']
        );

        BpjsConfig::query()->updateOrCreate(
            ['type' => 'jht'],
            ['employee_percentage' => '0.0200', 'company_percentage' => '0.0370']
        );

        BpjsConfig::query()->updateOrCreate(
            ['type' => 'jp'],
            ['employee_percentage' => '0.0100', 'company_percentage' => '0.0200']
        );

        // JKK (risk tier I demo rate), JKM, JKP — borne by the employer.
        BpjsConfig::query()->updateOrCreate(
            ['type' => 'jkk'],
            ['employee_percentage' => '0.0000', 'company_percentage' => '0.0024']
        );

        BpjsConfig::query()->updateOrCreate(
            ['type' => 'jkm'],
            ['employee_percentage' => '0.0000', 'company_percentage' => '0.0030']
        );

        BpjsConfig::query()->updateOrCreate(
            ['type' => 'jkp'],
            ['employee_percentage' => '0.0000', 'company_percentage' => '0.0046']
        );

        TaxRule::query()->updateOrCreate(
            ['name' => 'pph21_rate'],
            ['value' => '0.0500']
        );

        $this->seedTerTaxRules();
    }

    /**
     * Simplified PMK 168/2023 TER (Tarif Efektif Rata-rata) monthly brackets
     * for demo use only. Real production values must be sourced via the HR
     * research skill against PMK 168/2023, never hard-coded for live payroll.
     *
     * PTKP grouping (PMK 168/2023):
     *   - Category A: TK/0, TK/1, K/0
     *   - Category B: TK/2, TK/3, K/1, K/2
     *   - Category C: K/3
     */
    private function seedTerTaxRules(): void
    {
        $hasTerColumns = Schema::hasColumn('cfg_tax_rules', 'rule_type');

        $brackets = [
            'A' => [
                [0, 5_400_000, '0.0000'],
                [5_400_000, 6_000_000, '0.0025'],
                [6_000_000, 10_000_000, '0.0100'],
                [10_000_000, 50_000_000, '0.0500'],
                [50_000_000, null, '0.1500'],
            ],
            'B' => [
                [0, 6_200_000, '0.0000'],
                [6_200_000, 9_000_000, '0.0100'],
                [9_000_000, 15_000_000, '0.0300'],
                [15_000_000, 50_000_000, '0.0700'],
                [50_000_000, null, '0.1800'],
            ],
            'C' => [
                [0, 6_600_000, '0.0000'],
                [6_600_000, 10_000_000, '0.0150'],
                [10_000_000, 20_000_000, '0.0400'],
                [20_000_000, 50_000_000, '0.0900'],
                [50_000_000, null, '0.2000'],
            ],
        ];

        foreach ($brackets as $category => $rows) {
            foreach ($rows as $index => [$min, $max, $rate]) {
                $name = sprintf('ter_monthly_%s_%02d', $category, $index + 1);

                $attributes = ['value' => $rate];

                if ($hasTerColumns) {
                    $attributes += [
                        'rule_type' => 'ter_monthly',
                        'ptkp_category' => $category,
                        'gross_min' => $min,
                        'gross_max' => $max,
                    ];
                }

                TaxRule::query()->updateOrCreate(['name' => $name], $attributes);
            }
        }
    }

    private function seedEmployees(int $companyId, Generator $faker): void
    {
        $now = now();
        $rows = [];

        for ($i = 1; $i <= self::EMPLOYEE_COUNT; $i++) {
            $gender = $i % 2 === 0 ? 'female' : 'male';
            $rows[] = [
                'tenant_id' => null,
                'company_id' => $companyId,
                'employee_code' => sprintf('EMP-%05d', $i),
                'full_name' => $gender === 'female'
                    ? $faker->firstName('female').' '.$faker->lastName()
                    : $faker->firstName('male').' '.$faker->lastName(),
                'email' => IndonesianDemoData::workEmail($i),
                'phone' => IndonesianDemoData::indonesianMobile($faker),
                'gender' => $gender,
                'birth_date' => $faker->dateTimeBetween('-52 years', '-23 years')->format('Y-m-d'),
                'marital_status' => $faker->randomElement(['single', 'married', 'divorced', 'widowed']),
                'religion' => $faker->randomElement(IndonesianDemoData::RELIGIONS),
                'status' => 'active',
                'join_date' => $faker->dateTimeBetween('-6 years', '-3 months')->format('Y-m-d'),
                'resign_date' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('emp_employees')->insert($chunk);
        }
    }

    /**
     * Weekday attendance for the current month and the two previous calendar months.
     *
     * @param  Collection<int, Employee>  $employees
     */
    private function seedAttendanceForLastThreeMonths($employees, int $siteId, Generator $faker): void
    {
        $periodStart = Carbon::now()->startOfMonth()->subMonths(2);
        $periodEnd = Carbon::now()->endOfMonth();
        $period = CarbonPeriod::create($periodStart, $periodEnd);

        $batch = [];
        $batchSize = 800;

        foreach ($period as $date) {
            /** @var Carbon $date */
            if ($date->isWeekend()) {
                continue;
            }

            foreach ($employees as $employee) {
                $roll = $faker->numberBetween(1, 100);
                if ($roll <= 86) {
                    $status = 'present';
                } elseif ($roll <= 93) {
                    $status = 'late';
                } elseif ($roll <= 97) {
                    $status = 'leave';
                } else {
                    $status = 'sick';
                }

                $clockIn = null;
                $clockOut = null;
                if (in_array($status, ['present', 'late'], true)) {
                    $inHour = $status === 'late' ? $faker->numberBetween(8, 9) : $faker->numberBetween(7, 8);
                    $inMinute = $faker->numberBetween(0, 55);
                    $clockIn = $date->copy()->setTime($inHour, $inMinute, 0);
                    $outJitter = $faker->numberBetween(0, 40);
                    $clockOut = $date->copy()->setTime(17, $outJitter, 0);
                }

                $batch[] = [
                    'employee_id' => $employee->id,
                    'site_id' => $siteId,
                    'clock_in' => $clockIn,
                    'clock_out' => $clockOut,
                    'latitude' => $faker->latitude(-6.35, -6.12),
                    'longitude' => $faker->longitude(106.75, 106.98),
                    'status' => $status,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (count($batch) >= $batchSize) {
                    DB::table('att_attendances')->insert($batch);
                    $batch = [];
                }
            }
        }

        if ($batch !== []) {
            DB::table('att_attendances')->insert($batch);
        }
    }

    /**
     * @param  Collection<int, Employee>  $employees
     */
    private function seedPayrollsForLastThreeMonths($employees): void
    {
        /** @var PayrollCalculationService $service */
        $service = app(PayrollCalculationService::class);

        $months = [
            [Carbon::now()->copy()->subMonths(2)->month, Carbon::now()->copy()->subMonths(2)->year],
            [Carbon::now()->copy()->subMonths(1)->month, Carbon::now()->copy()->subMonths(1)->year],
            [Carbon::now()->month, Carbon::now()->year],
        ];

        foreach ($employees as $employee) {
            $baseSalary = 7_000_000 + ($employee->id % 160) * 25_000;

            foreach ($months as [$month, $year]) {
                $service->generate($employee, (int) $month, (int) $year, (float) $baseSalary);
            }
        }
    }
}
