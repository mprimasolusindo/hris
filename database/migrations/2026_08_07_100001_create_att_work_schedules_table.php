<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('att_work_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 32)->unique();
            $table->boolean('is_default')->default(false);
            $table->time('default_start_time')->nullable();
            $table->time('default_end_time')->nullable();
            $table->boolean('mon_working')->default(true);
            $table->boolean('tue_working')->default(true);
            $table->boolean('wed_working')->default(true);
            $table->boolean('thu_working')->default(true);
            $table->boolean('fri_working')->default(true);
            $table->boolean('sat_working')->default(false);
            $table->boolean('sun_working')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('att_work_schedules');
    }
};
