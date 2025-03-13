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
        Schema::create('hijri_years', function (Blueprint $table) {
            $table->id();
            $table->string('year')->nullable();
            $table->boolean('is_current')->default(false); 
            $table->integer('start_date');
            $table->integer('end_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hijri_years');
    }
};
