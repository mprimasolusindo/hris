<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('emp_tax_profiles', function (Blueprint $table) {
            $table->boolean('has_npwp')->default(false)->after('employee_id');
            $table->string('npwp', 32)->nullable()->after('has_npwp');
            $table->string('tax_method', 32)->default('ter_monthly')->after('tax_status');
        });
    }

    public function down(): void
    {
        Schema::table('emp_tax_profiles', function (Blueprint $table) {
            $table->dropColumn(['has_npwp', 'npwp', 'tax_method']);
        });
    }
};
