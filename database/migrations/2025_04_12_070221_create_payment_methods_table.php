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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id(); 
            $table->string('name');
            $table->string('icon')->nullable(); 
            $table->text('info')->nullable(); 
            $table->decimal('income_in_hand', 15, 2)->default(0); 
            $table->decimal('expense_in_hand', 15, 2)->default(0); 
            $table->decimal('balance', 15, 2)->default(0);   
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
