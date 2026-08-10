<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('att_holidays', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('holiday_date');
            $table->string('type', 32); // national | company | joint_leave
            $table->boolean('is_half_day')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique('holiday_date');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('att_holidays');
    }
};
