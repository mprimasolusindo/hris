<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emp_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('emp_employees')->cascadeOnDelete();
            $table->foreignId('component_id')->nullable()->constrained('cfg_salary_components')->nullOnDelete();
            $table->string('name');
            $table->decimal('value', 18, 2)->default(0);
            $table->date('effective_start')->nullable();
            $table->date('effective_end')->nullable();
            $table->string('status', 16)->default('active');
            $table->boolean('recurring')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emp_deductions');
    }
};
