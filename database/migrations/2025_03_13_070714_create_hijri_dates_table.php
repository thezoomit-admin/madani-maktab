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
        Schema::create('hijri_dates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hijri_year_id')->constrained('hijri_years');
            $table->string('month');
            $table->date('start_date');
            $table->date('end_date')->nullable(); 
            
            $table->boolean('is_active')->default(false); 
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->constrained('users');
            $table->timestamps(); 

            $table->unique(['hijri_year_id', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hijri_dates');
    }
};
