<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lv_leaves', function (Blueprint $table) {
            $table->text('reason')->nullable()->after('end_date');
            $table->foreignId('approved_by')
                ->nullable()
                ->after('status')
                ->constrained('emp_employees')
                ->nullOnDelete();
            $table->dateTime('decided_at')->nullable()->after('approved_by');
        });
    }

    public function down(): void
    {
        Schema::table('lv_leaves', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['reason', 'decided_at']);
        });
    }
};
