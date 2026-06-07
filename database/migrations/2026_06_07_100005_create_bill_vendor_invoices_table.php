<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * bill_vendor_invoices: append-only billing records issued to/by outsourcing
 * vendors (org_companies acting as vendor) for a service period. Transactional
 * ledger — no soft delete.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bill_vendor_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('org_companies')->restrictOnDelete();
            $table->foreignId('tenant_id')->nullable()->constrained('sys_tenants')->nullOnDelete();
            $table->string('invoice_number')->unique();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('amount', 18, 2);
            $table->string('status', 32)->default('draft');
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_vendor_invoices');
    }
};
