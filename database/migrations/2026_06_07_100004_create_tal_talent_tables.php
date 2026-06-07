<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Talent & Growth domain tables: performance reviews, trainings (and their
 * employee enrollment pivot), talent pool, succession plans, and nine-box
 * assessments.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tal_performance_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('emp_employees')->cascadeOnDelete();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('period_year');
            $table->integer('period_quarter');
            $table->decimal('rating', 3, 2);
            $table->text('goals')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 32);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tal_trainings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('location')->nullable();
            $table->string('status', 32);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('rel_training_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_id')->constrained('tal_trainings')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('emp_employees')->cascadeOnDelete();
            $table->string('status', 32)->default('registered');
            $table->timestamps();
            $table->unique(['training_id', 'employee_id']);
        });

        Schema::create('tal_talent_pool', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('emp_employees')->cascadeOnDelete();
            $table->string('readiness', 32);
            $table->string('potential', 32);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tal_succession_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('position_id')->constrained('org_positions')->restrictOnDelete();
            $table->foreignId('successor_id')->constrained('emp_employees')->cascadeOnDelete();
            $table->foreignId('incumbent_id')->nullable()->references('id')->on('emp_employees')->nullOnDelete();
            $table->string('readiness', 32);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tal_nine_box_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('emp_employees')->cascadeOnDelete();
            $table->integer('period_year');
            $table->integer('performance_score');
            $table->integer('potential_score');
            $table->string('box_label')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tal_nine_box_assessments');
        Schema::dropIfExists('tal_succession_plans');
        Schema::dropIfExists('tal_talent_pool');
        Schema::dropIfExists('rel_training_employees');
        Schema::dropIfExists('tal_trainings');
        Schema::dropIfExists('tal_performance_reviews');
    }
};
