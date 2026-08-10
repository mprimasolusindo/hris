<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Expand trx_jobs to match Lovable JobPostingFormDialog fields.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trx_jobs', function (Blueprint $table) {
            $table->string('code', 64)->nullable()->after('title');
            $table->foreignId('site_id')
                ->nullable()
                ->after('company_id')
                ->constrained('org_sites')
                ->nullOnDelete();
            $table->foreignId('department_id')
                ->nullable()
                ->after('site_id')
                ->constrained('org_departments')
                ->nullOnDelete();
            $table->foreignId('position_id')
                ->nullable()
                ->after('department_id')
                ->constrained('org_positions')
                ->nullOnDelete();
            $table->string('employment_type', 32)->default('permanent')->after('code');
            $table->unsignedInteger('headcount')->default(1)->after('employment_type');
            $table->unsignedInteger('filled_count')->default(0)->after('headcount');
            $table->string('priority', 16)->default('medium')->after('status');
            $table->date('opened_at')->nullable()->after('priority');
            $table->date('target_close_date')->nullable()->after('opened_at');
            $table->dateTime('closed_at')->nullable()->after('target_close_date');
            $table->foreignId('hiring_manager_id')
                ->nullable()
                ->after('closed_at')
                ->constrained('emp_employees')
                ->nullOnDelete();
            $table->foreignId('recruiter_id')
                ->nullable()
                ->after('hiring_manager_id')
                ->constrained('emp_employees')
                ->nullOnDelete();
            $table->decimal('salary_min', 18, 2)->nullable()->after('recruiter_id');
            $table->decimal('salary_max', 18, 2)->nullable()->after('salary_min');
            $table->string('currency', 8)->default('IDR')->after('salary_max');
            $table->text('description')->nullable()->after('currency');
            $table->text('requirements')->nullable()->after('description');
            $table->text('benefits')->nullable()->after('requirements');
            $table->string('location_note')->nullable()->after('benefits');
            $table->unique(['company_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::table('trx_jobs', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'code']);
            $table->dropConstrainedForeignId('site_id');
            $table->dropConstrainedForeignId('department_id');
            $table->dropConstrainedForeignId('position_id');
            $table->dropConstrainedForeignId('hiring_manager_id');
            $table->dropConstrainedForeignId('recruiter_id');
            $table->dropColumn([
                'code',
                'employment_type',
                'headcount',
                'filled_count',
                'priority',
                'opened_at',
                'target_close_date',
                'closed_at',
                'salary_min',
                'salary_max',
                'currency',
                'description',
                'requirements',
                'benefits',
                'location_note',
            ]);
        });
    }
};
