<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sub_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 18, 2)->default(0);
            $table->unsignedInteger('employee_limit')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_plans');
    }
};
