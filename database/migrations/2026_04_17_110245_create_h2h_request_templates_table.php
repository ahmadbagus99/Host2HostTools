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
        Schema::create('h2h_request_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('h2h_system_id')->constrained('h2h_systems')->cascadeOnDelete();
            $table->string('name');
            $table->string('description')->nullable();
            $table->longText('request_body');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('h2h_request_templates');
    }
};
