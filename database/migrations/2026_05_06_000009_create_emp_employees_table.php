<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emp_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('sys_tenants')->nullOnDelete();
            $table->foreignId('company_id')->constrained('org_companies')->restrictOnDelete();
            $table->string('employee_code');
            $table->string('full_name');
            $table->string('email')->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('gender', 16)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('marital_status', 16)->nullable();
            $table->string('religion', 32)->nullable();
            $table->string('status', 32)->default('active');
            $table->date('join_date')->nullable();
            $table->date('resign_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['tenant_id', 'employee_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emp_employees');
    }
};
