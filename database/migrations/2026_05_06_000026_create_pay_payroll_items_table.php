<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * pay_payroll_items.type values: earning | deduction
 * Append-only: no soft deletes. Once payroll is posted, items are immutable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pay_payroll_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained('pay_payrolls')->cascadeOnDelete();
            $table->string('component_name');
            $table->string('type', 16);
            $table->decimal('amount', 18, 2);
            $table->timestamps();
            $table->index(['payroll_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pay_payroll_items');
    }
};
