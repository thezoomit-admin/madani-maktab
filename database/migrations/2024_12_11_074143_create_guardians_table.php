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
        Schema::create('guardians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('student_id')->constrained();  
            $table->string('guardian_name');
            $table->string('guardian_relation');
            $table->string('guardian_occupation');
            $table->string('guardian_education');
            $table->string('guardian_workplace');
            $table->integer('children_count')->nullable();
            $table->string('child_1_education')->nullable();
            $table->string('contact_number_1', 15)->nullable();
            $table->string('contact_number_2', 15)->nullable();
            $table->string('whatsapp_number', 15)->nullable();
            $table->boolean('same_address')->default(false); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guardians');
    }
};
