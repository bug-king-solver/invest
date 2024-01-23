<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('type', ['admin', 'merchant'])->after('password');
            $table->string('city')->nullable()->default(null)->after('type');
            $table->string('state')->nullable()->default(null)->after('city');
            $table->string('address')->nullable()->default(null)->after('state');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['type', 'city', 'state', 'address']);
        });
    }
};
