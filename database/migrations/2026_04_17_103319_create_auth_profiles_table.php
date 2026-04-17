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
        Schema::create('auth_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('auth_type');
            $table->string('token')->nullable();
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->string('api_key')->nullable();
            $table->string('api_key_header')->nullable();
            $table->json('extra_headers')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth_profiles');
    }
};
