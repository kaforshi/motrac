<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('notify_security')->default(true)->after('currency');
            $table->boolean('notify_budget')->default(true)->after('notify_security');
            $table->boolean('notify_weekly_report')->default(false)->after('notify_budget');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['notify_security', 'notify_budget', 'notify_weekly_report']);
        });
    }
};
