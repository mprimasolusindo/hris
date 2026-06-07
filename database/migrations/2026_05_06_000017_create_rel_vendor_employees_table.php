<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rel_vendor_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('emp_employees')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('org_companies')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['employee_id', 'vendor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rel_vendor_employees');
    }
};
