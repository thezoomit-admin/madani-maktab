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
        Schema::create('interview_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('users')->onDelete('cascade'); 
            $table->foreignId('interviewer_id')->nullable()->constrained('users')->onDelete('cascade'); 
            $table->dateTime('requested_at');
            $table->dateTime('attended_at')->nullable();
            $table->enum('location', ['online', 'office', 'offsite'])->default('online'); 
            $table->enum('status', ['pending', 'completed', 'canceled'])->default('pending');
            $table->string('meeting_link')->nullable();
            $table->text('notes')->nullable(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interview_schedules');
    }
};
