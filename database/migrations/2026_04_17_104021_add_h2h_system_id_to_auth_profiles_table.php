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
        Schema::table('auth_profiles', function (Blueprint $table) {
            $table->foreignId('h2h_system_id')->nullable()->after('id')->constrained('h2h_systems')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auth_profiles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('h2h_system_id');
        });
    }
};
