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
            $table->boolean('is_interested')->default(true)->nullable();
            $table->boolean('is_passed_age')->nullable(); 
            $table->boolean('is_send_step_2_link')->nullable();
            $table->boolean('is_registration_complete')->nullable();
            $table->boolean('is_interview_scheduled')->nullable();
            $table->boolean('is_first_exam_completed')->nullable();
            $table->boolean('is_passed_interview')->nullable();  
            $table->boolean('is_invited_for_trial')->nullable();
            $table->boolean('is_present_in_madrasa')->nullable();
            $table->boolean('is_passed_trial')->nullable();
            $table->boolean('is_admission_completed')->nullable();
            
            $table->boolean('is_send_fail_message')->nullable();
            $table->boolean('is_send_final_pass_message')->nullable();
            $table->boolean('is_print_profile')->default(false)->nullable();
            $table->timestamps();
        });
    }
 
    //is_first_exam_completed
    //is_present_in_madrasa
    //is_admission_completed

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admission_progress_statuses');
    }
};
