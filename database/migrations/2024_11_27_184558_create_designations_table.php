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
        Schema::create('designations', function (Blueprint $table) {
            $table->id(); 
            $table->string('title'); 
            $table->string('slug'); 
            $table->string('department')->nullable();  
            $table->string('level')->nullable(); 
            $table->decimal('salary_range_min', 15, 2)->nullable(); 
            $table->decimal('salary_range_max', 15, 2)->nullable(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('designations');
    }
};
