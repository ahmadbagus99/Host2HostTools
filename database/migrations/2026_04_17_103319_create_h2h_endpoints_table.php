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
        Schema::create('h2h_endpoints', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('base_url');
            $table->string('path')->default('/');
            $table->string('method')->default('POST');
            $table->unsignedInteger('timeout_seconds')->default(30);
            $table->foreignId('auth_profile_id')->nullable()->constrained('auth_profiles')->nullOnDelete();
            $table->json('default_headers')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('h2h_endpoints');
    }
};
