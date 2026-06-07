<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emp_identities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('emp_employees')->cascadeOnDelete();
            $table->string('nik', 32)->nullable();
            $table->string('npwp', 32)->nullable();
            $table->string('bpjs_health', 32)->nullable();
            $table->string('bpjs_employment', 32)->nullable();
            $table->string('address')->nullable();
            $table->string('city', 64)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emp_identities');
    }
};
