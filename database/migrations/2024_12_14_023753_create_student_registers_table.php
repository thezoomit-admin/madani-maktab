<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{  
    public function up(): void
    {
        Schema::create('student_registers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(); 
            $table->string('reg_id')->nullable();  
            $table->string('name');
            $table->string('father_name'); 
            $table->integer('department_id');
            $table->string('bangla_study_status')->nullable();
            $table->string('bangla_others_study')->nullable();
            $table->string('arabi_study_status')->nullable();
            $table->string('arabi_others_study')->nullable();  
            $table->string('handwriting_image')->nullable();
            $table->boolean('is_health_issue')->default(0)->comment('1= yes, 0 = no');
            
            // For Maktab-specific data
            $table->text('previous_education_details')->nullable();

            // For kitab study
            $table->string('hifz_para')->nullable();
            $table->integer('is_other_kitab_study')->nullable();
            $table->string('kitab_jamat')->nullable();
            $table->integer('is_bangla_handwriting_clear')->nullable(); 
            $table->text('note')->nullable();
            $table->boolean('is_existing')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_registers');
    }
};
