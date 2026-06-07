<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * trx_interviews: scheduled interviews tied to a recruitment application,
 * capturing interviewer, logistics, outcome status, feedback and rating.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trx_interviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('trx_applications')->cascadeOnDelete();
            $table->dateTime('scheduled_at');
            $table->string('interviewer_name');
            $table->string('location')->nullable();
            $table->string('status', 32)->default('scheduled');
            $table->text('feedback')->nullable();
            $table->integer('rating')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trx_interviews');
    }
};
