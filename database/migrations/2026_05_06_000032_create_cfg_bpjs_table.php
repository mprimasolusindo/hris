<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * cfg_bpjs.type expected values (rows seeded later via the HR research skill):
 *   - kesehatan : BPJS Kesehatan        (Perpres 64/2020)
 *   - jht       : Jaminan Hari Tua      (PP 46/2015)
 *   - jp        : Jaminan Pensiun       (PP 45/2015)
 *   - jkk       : Jaminan Kecelakaan Kerja (PP 44/2015, risk-tier based)
 *   - jkm       : Jaminan Kematian      (PP 44/2015)
 *   - jkp       : Jaminan Kehilangan Pekerjaan (PP 37/2021)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cfg_bpjs', function (Blueprint $table) {
            $table->id();
            $table->string('type', 32);
            $table->decimal('employee_percentage', 7, 4)->default(0);
            $table->decimal('company_percentage', 7, 4)->default(0);
            $table->timestamps();
            $table->unique('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cfg_bpjs');
    }
};
