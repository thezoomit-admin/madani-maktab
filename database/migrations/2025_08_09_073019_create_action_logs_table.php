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
        Schema::create('action_logs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained('users'); 
        $table->string('method');
        $table->string('route');
        $table->string('action');
        
        // Client Details
        $table->string('ip_address')->nullable();
        $table->string('user_agent')->nullable();
        $table->string('browser_url')->nullable();
        $table->timestamp('accessed_at')->nullable();

        // Server Details
        $table->string('hostname')->nullable();
        $table->string('platform')->nullable();
        $table->string('uptime')->nullable();

        $table->integer('request_status_code')->nullable();
        $table->integer('response_status_code')->nullable();
        $table->timestamp('timestamp')->nullable(); 
        $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('action_logs');
    }
};
