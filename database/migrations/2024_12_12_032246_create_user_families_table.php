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
        Schema::create('user_families', function (Blueprint $table) {
            $table->id(); 
            $table->foreignId('user_id')->constrained();
            $table->string('deeni_steps', 255); 
            $table->boolean('is_follow_porada'); 
            $table->boolean('is_shariah_compliant'); 
            $table->string('motivation', 255); 
            $table->string('info_src', 255); 
            $table->date('first_contact'); 
            $table->string('preparation', 255)->nullable(); 
            $table->boolean('is_clean_lang'); 
            $table->string('future_plan', 255)->nullable(); 
            $table->integer('years_at_inst'); 
            $table->string('reason_diff_edu', 2048)->nullable(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_families');
    }
};
