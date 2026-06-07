<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * cfg_tax_rules: storage for PPh21 brackets, PTKP thresholds, TER rates,
 * etc. Values must be sourced via the HR research skill (PMK 168/2023,
 * UU 7/2021 HPP, etc.) and never hard-coded in application logic.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cfg_tax_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('value', 18, 4)->default(0);
            $table->timestamps();
            $table->unique('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cfg_tax_rules');
    }
};
