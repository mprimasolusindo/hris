<?php

namespace App\Actions\Employee;

use App\Models\Employee;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportEmployeesAction
{
    /**
     * @param  array<int>|null  $ids
     */
    public function __invoke(?array $ids = null): StreamedResponse
    {
        $query = Employee::query()->with('company:id,name');

        if ($ids !== null && count($ids) > 0) {
            $query->whereIn('id', $ids);
        }

        $filename = 'employees-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'id', 'company_id', 'company_name', 'employee_code', 'full_name',
                'email', 'phone', 'status', 'join_date', 'resign_date',
            ]);

            $query->orderBy('id')->chunk(200, function ($employees) use ($handle) {
                foreach ($employees as $employee) {
                    fputcsv($handle, [
                        $employee->id,
                        $employee->company_id,
                        $employee->company?->name,
                        $employee->employee_code,
                        $employee->full_name,
                        $employee->email,
                        $employee->phone,
                        $employee->status,
                        $employee->join_date?->toDateString(),
                        $employee->resign_date?->toDateString(),
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
