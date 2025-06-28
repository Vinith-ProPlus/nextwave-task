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
        Schema::create('api_logs', function (Blueprint $table) {
            $table->id();
            $table->string('method');
            $table->string('endpoint');
            $table->json('request_data')->nullable();
            $table->integer('response_status')->nullable();
            $table->text('user_agent')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamp('created_at');
            
            $table->index(['method', 'endpoint']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_logs');
    }
};
