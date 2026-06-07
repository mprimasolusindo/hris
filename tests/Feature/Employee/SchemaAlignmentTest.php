<?php

namespace Tests\Feature\Employee;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SchemaAlignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_emp_allowances_has_catalog_columns(): void
    {
        $this->assertTrue(Schema::hasTable('emp_allowances'));
        $this->assertTrue(Schema::hasColumns('emp_allowances', [
            'component_id',
            'taxable',
            'effective_start',
            'effective_end',
            'status',
            'recurring',
        ]));
    }

    public function test_emp_deductions_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('emp_deductions'));
        $this->assertTrue(Schema::hasColumns('emp_deductions', [
            'employee_id',
            'component_id',
            'name',
            'value',
            'effective_start',
            'effective_end',
            'status',
            'recurring',
        ]));
    }

    public function test_emp_tax_profiles_has_extended_tax_fields(): void
    {
        $this->assertTrue(Schema::hasColumns('emp_tax_profiles', [
            'has_npwp',
            'tax_method',
            'npwp',
        ]));
    }

    public function test_emp_employees_has_photo_and_user_link(): void
    {
        $this->assertTrue(Schema::hasColumns('emp_employees', [
            'profile_photo_path',
            'user_id',
        ]));
    }

    public function test_emp_documents_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('emp_documents'));
    }
}
