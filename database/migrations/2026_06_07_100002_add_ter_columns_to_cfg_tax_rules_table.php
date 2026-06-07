<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds TER (Tarif Efektif Rata-rata, PMK 168/2023) descriptor columns to
 * cfg_tax_rules so a single row can express a bracket: rule_type + PTKP
 * category + gross income band. Values must be sourced via the HR research
 * skill; never hard-coded in application logic.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cfg_tax_rules', function (Blueprint $table) {
            $table->string('rule_type')->nullable()->after('name');
            $table->string('ptkp_category')->nullable()->after('rule_type');
            $table->decimal('gross_min', 18, 2)->nullable()->after('ptkp_category');
            $table->decimal('gross_max', 18, 2)->nullable()->after('gross_min');
        });
    }

    public function down(): void
    {
        Schema::table('cfg_tax_rules', function (Blueprint $table) {
            $table->dropColumn(['rule_type', 'ptkp_category', 'gross_min', 'gross_max']);
        });
    }
};
