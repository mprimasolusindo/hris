<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pay_payrolls', function (Blueprint $table) {
            $table->string('status', 32)->default('generated')->after('net_salary');
            $table->text('approval_notes')->nullable()->after('status');

            $table->foreignId('reviewed_by')->nullable()->after('approval_notes')
                ->constrained('users')->nullOnDelete();
            $table->dateTime('reviewed_at')->nullable()->after('reviewed_by');

            $table->foreignId('approved_by')->nullable()->after('reviewed_at')
                ->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable()->after('approved_by');

            $table->foreignId('paid_by')->nullable()->after('approved_at')
                ->constrained('users')->nullOnDelete();
            $table->dateTime('paid_at')->nullable()->after('paid_by');

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('pay_payrolls', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropConstrainedForeignId('approved_by');
            $table->dropConstrainedForeignId('paid_by');
            $table->dropColumn([
                'reviewed_at',
                'approved_at',
                'paid_at',
                'approval_notes',
                'status',
            ]);
        });
    }
};
