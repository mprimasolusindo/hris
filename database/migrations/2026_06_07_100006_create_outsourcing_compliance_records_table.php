<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * outsourcing_compliance_records: tracks alih daya / outsourcing compliance
 * flags raised against a vendor-supplied employee (e.g. expired contract,
 * missing BPJS), with resolution workflow.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outsourcing_compliance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('emp_employees')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('org_companies')->restrictOnDelete();
            $table->string('flag_type');
            $table->text('description');
            $table->string('status', 32)->default('open');
            $table->dateTime('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outsourcing_compliance_records');
    }
};
