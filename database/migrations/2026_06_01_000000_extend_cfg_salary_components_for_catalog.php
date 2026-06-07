<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * calculation_method values: fixed | percentage | formula
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cfg_salary_components', function (Blueprint $table) {
            $table->string('code', 64)->nullable()->after('name');
            $table->string('calculation_method', 16)->default('fixed')->after('type');
            $table->decimal('default_value', 18, 2)->default(0)->after('calculation_method');
        });
    }

    public function down(): void
    {
        Schema::table('cfg_salary_components', function (Blueprint $table) {
            $table->dropColumn(['code', 'calculation_method', 'default_value']);
        });
    }
};
