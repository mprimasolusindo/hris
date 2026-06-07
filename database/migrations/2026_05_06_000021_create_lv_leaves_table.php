<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lv_leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('emp_employees')->cascadeOnDelete();
            $table->string('type', 32);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 32)->default('pending');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['employee_id', 'start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lv_leaves');
    }
};
