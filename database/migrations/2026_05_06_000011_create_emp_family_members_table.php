<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emp_family_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('emp_employees')->cascadeOnDelete();
            $table->string('name');
            $table->string('relationship', 32);
            $table->date('birth_date')->nullable();
            $table->boolean('is_dependent')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emp_family_members');
    }
};
