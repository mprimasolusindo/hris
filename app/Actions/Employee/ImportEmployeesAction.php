<?php

namespace App\Actions\Employee;

use App\Models\Employee;
use App\Services\Employee\EmployeeService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ImportEmployeesAction
{
    public function __construct(private EmployeeService $employees) {}

    /**
     * @return array{imported: int, errors: array<int, string>}
     */
    public function __invoke(UploadedFile $file, bool $dryRun = false): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle);
        $imported = 0;
        $errors = [];
        $rowNum = 1;

        if (! $header) {
            fclose($handle);

            return ['imported' => 0, 'errors' => [1 => 'Empty CSV file.']];
        }

        $map = array_flip(array_map('strtolower', array_map('trim', $header)));

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            $data = $this->mapRow($row, $map);

            if ($data === null) {
                $errors[$rowNum] = 'Missing required columns (company_id, employee_code, full_name).';

                continue;
            }

            if ($dryRun) {
                $imported++;

                continue;
            }

            try {
                DB::transaction(fn () => $this->employees->create($data));
                $imported++;
            } catch (\Throwable $e) {
                $errors[$rowNum] = $e->getMessage();
            }
        }

        fclose($handle);

        return ['imported' => $imported, 'errors' => $errors];
    }

    /**
     * @param  array<int, string>  $row
     * @param  array<string, int>  $map
     * @return array<string, mixed>|null
     */
    private function mapRow(array $row, array $map): ?array
    {
        $get = fn (string $key) => isset($map[$key]) ? trim((string) ($row[$map[$key]] ?? '')) : '';

        $companyId = $get('company_id');
        $code = $get('employee_code');
        $name = $get('full_name');

        if ($companyId === '' || $code === '' || $name === '') {
            return null;
        }

        return [
            'company_id' => (int) $companyId,
            'employee_code' => $code,
            'full_name' => $name,
            'email' => $get('email') ?: null,
            'phone' => $get('phone') ?: null,
            'status' => $get('status') ?: 'active',
            'join_date' => $get('join_date') ?: null,
        ];
    }
}
