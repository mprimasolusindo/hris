<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('emp_employees', function (Blueprint $table) {
            $table->string('profile_photo_path')->nullable()->after('resign_date');
            $table->foreignId('user_id')->nullable()->after('profile_photo_path')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('emp_employees', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['profile_photo_path', 'user_id']);
        });
    }
};
