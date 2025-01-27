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
            $table->boolean('is_registration_complete')->nullable()->comment("null=not send link, 0 = send but not fillup, 1 = fillup");
            $table->boolean('is_interview_scheduled')->nullable();
            $table->boolean('is_passed_interview')->nullable();  
            $table->boolean('is_invited_for_trial')->nullable();
            $table->boolean('is_passed_trial')->nullable();
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
