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
        Schema::create('h2h_test_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('h2h_endpoint_id')->constrained('h2h_endpoints')->cascadeOnDelete();
            $table->string('request_url');
            $table->string('request_method');
            $table->json('request_headers')->nullable();
            $table->longText('request_body')->nullable();
            $table->unsignedInteger('response_status')->nullable();
            $table->json('response_headers')->nullable();
            $table->longText('response_body')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('h2h_test_runs');
    }
};
