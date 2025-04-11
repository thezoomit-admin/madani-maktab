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
        Schema::create('enroles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users'); 
            $table->foreignId('student_id')->constrained('students'); 
            $table->integer('department_id')->nullable();
            $table->string('session')->nullable(); 
            $table->string('year')->nullable(); 
            $table->string('marks')->nullable(); 
            $table->enum('fee_type', ['General', 'Half', 'Guest'])->default('General');  
            $table->decimal('fee', 10, 2)->nullable()->comment("if half"); 
            $table->tinyInteger('status')->default(0)->comment('1= Running, 2= Completed, 0 = Rejected'); 
            $table->timestamps(); 
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enroles');
    }
};
