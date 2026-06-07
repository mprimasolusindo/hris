<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Recruitment job postings (separate from Laravel's framework "jobs" queue table).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trx_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('org_companies')->cascadeOnDelete();
            $table->string('title');
            $table->string('status', 32)->default('open');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trx_jobs');
    }
};
