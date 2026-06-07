<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emp_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('emp_employees')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('org_companies')->restrictOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('org_departments')->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('org_positions')->nullOnDelete();
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->string('employment_type', 32)->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('manager_id')->references('id')->on('emp_employees')->nullOnDelete();
            $table->index(['employee_id', 'start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emp_jobs');
    }
};
