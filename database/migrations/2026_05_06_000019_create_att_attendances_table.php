<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('att_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('emp_employees')->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('org_sites')->nullOnDelete();
            $table->dateTime('clock_in')->nullable();
            $table->dateTime('clock_out')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('status', 32)->default('present');
            $table->timestamps();
            $table->index(['employee_id', 'clock_in']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('att_attendances');
    }
};
