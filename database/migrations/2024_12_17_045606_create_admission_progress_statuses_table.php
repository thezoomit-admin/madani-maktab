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
        Schema::create('admission_progress_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->boolean('is_passed_age')->nullable(); 
            $table->boolean('is_interview_scheduled')->nullable();  
            $table->boolean('is_passed_interview')->nullable();  
            $table->boolean('is_invited_for_visit')->nullable();
            $table->boolean('is_passed_final')->nullable(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admission_progress_statuses');
    }
};
