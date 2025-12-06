<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('photo')->nullable()->after('email');
            $table->text('bio')->nullable()->after('photo');
            $table->string('currency', 3)->default('IDR')->after('bio');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['photo', 'bio', 'currency']);
        });
    }
};




