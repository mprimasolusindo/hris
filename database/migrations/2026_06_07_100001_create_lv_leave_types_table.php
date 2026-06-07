<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * lv_leave_types: master catalog of leave categories (annual, sick, etc.)
 * with default annual entitlement and paid/unpaid flag.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lv_leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('name');
            $table->integer('annual_entitlement_days')->default(0);
            $table->boolean('is_paid')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lv_leave_types');
    }
};
