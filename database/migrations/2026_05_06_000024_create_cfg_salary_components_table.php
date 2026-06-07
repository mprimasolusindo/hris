<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * cfg_salary_components.type values:
 *   - earning   (gaji pokok, tunjangan, bonus, lembur)
 *   - deduction (BPJS, PPh21, koperasi, denda, pinjaman)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cfg_salary_components', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type', 16);
            $table->boolean('is_taxable')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cfg_salary_components');
    }
};
