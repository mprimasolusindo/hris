<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\EmployeeDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeDocument>
 */
class EmployeeDocumentFactory extends Factory
{
    protected $model = EmployeeDocument::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'category' => 'other',
            'file_path' => 'employees/documents/sample.pdf',
            'original_name' => 'sample.pdf',
            'mime_type' => 'application/pdf',
            'size' => 1024,
            'uploaded_by' => null,
        ];
    }
}
