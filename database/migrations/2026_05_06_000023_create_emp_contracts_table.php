<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * emp_contracts.contract_type values:
 *   - pkwt        : fixed-term contract (PP 35/2021 ps. 8)
 *   - pkwtt       : permanent employment
 *   - outsourcing : alih daya (PP 35/2021 ps. 18-19)
 *   - magang      : intern / apprenticeship (Permenaker 6/2020)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emp_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('emp_employees')->cascadeOnDelete();
            $table->string('contract_type', 32);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('salary_base', 18, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['employee_id', 'start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emp_contracts');
    }
};
