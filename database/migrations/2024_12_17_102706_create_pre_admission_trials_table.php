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
        Schema::create('pre_admission_trials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('users')->onDelete('cascade'); 
            $table->foreignId('teacher_id')->nullable()->constrained('users')->onDelete('cascade'); 
            $table->dateTime('requested_at');  
            $table->dateTime('attended_at')->nullable();
            $table->enum('status', ['pending', 'attended', 'completed'])->default('pending'); 
            $table->boolean('result')->nullable(); 
            $table->text('note');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pre_admission_trials');
    }
};
