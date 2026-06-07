<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('emp_allowances', function (Blueprint $table) {
            $table->foreignId('component_id')->nullable()->after('employee_id')
                ->constrained('cfg_salary_components')->nullOnDelete();
            $table->boolean('taxable')->default(true)->after('amount');
            $table->date('effective_start')->nullable()->after('taxable');
            $table->date('effective_end')->nullable()->after('effective_start');
            $table->string('status', 16)->default('active')->after('effective_end');
            $table->boolean('recurring')->default(true)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('emp_allowances', function (Blueprint $table) {
            $table->dropForeign(['component_id']);
            $table->dropColumn([
                'component_id',
                'taxable',
                'effective_start',
                'effective_end',
                'status',
                'recurring',
            ]);
        });
    }
};
