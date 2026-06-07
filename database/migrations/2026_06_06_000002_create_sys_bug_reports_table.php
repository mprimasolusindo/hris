<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * status values: todo | in_progress | failed | ready_for_review | on_review | closed | done
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sys_bug_reports', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status', 32)->default('todo');
            $table->string('url');
            $table->string('page_title')->nullable();
            $table->longText('console_log')->nullable();
            $table->string('user_agent')->nullable();
            $table->unsignedInteger('viewport_width')->nullable();
            $table->unsignedInteger('viewport_height')->nullable();
            $table->string('screenshot_path')->nullable();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sys_bug_reports');
    }
};
