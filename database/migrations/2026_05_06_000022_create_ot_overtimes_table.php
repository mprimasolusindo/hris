<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ot_overtimes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('emp_employees')->cascadeOnDelete();
            $table->date('date');
            $table->decimal('hours', 6, 2)->default(0);
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->string('status', 32)->default('pending');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('approved_by')->references('id')->on('emp_employees')->nullOnDelete();
            $table->index(['employee_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ot_overtimes');
    }
};
