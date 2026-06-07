<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trx_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('trx_candidates')->cascadeOnDelete();
            $table->foreignId('job_id')->constrained('trx_jobs')->cascadeOnDelete();
            $table->string('stage', 32)->default('applied');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['candidate_id', 'job_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trx_applications');
    }
};
