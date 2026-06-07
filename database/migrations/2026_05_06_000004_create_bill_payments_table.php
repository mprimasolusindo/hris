<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bill_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('sys_tenants')->cascadeOnDelete();
            $table->decimal('amount', 18, 2);
            $table->string('method', 32)->nullable();
            $table->string('status', 32)->default('pending');
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_payments');
    }
};
