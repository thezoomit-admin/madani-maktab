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
        Schema::create('admissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(); 
            $table->string('reg_id')->nullable();
            $table->string('name')->nullable();
            $table->string('father_name')->nullable(); 
            $table->integer('department_id')->nullable();
            $table->string('interested_session')->nullable();
            $table->string('last_year_session')->nullable();
            $table->string('last_year_id')->nullable();
            $table->string('original_id')->nullable();
            $table->string('total_marks')->nullable();
            $table->string('average_marks')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0= Unapproved, 1= Running, 2= Completed, 3 Rejected');
            $table->integer('student_id')->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admissions');
    }
};
