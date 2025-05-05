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
        Schema::create('office_transactions', function (Blueprint $table) {
            $table->id();
            $table->integer('type')->comment('1=Joma, 2 = uttolon');
            $table->foreignId('payment_method_id')->constrained('payment_methods');
            $table->decimal('amount');
            $table->text('description');
            $table->text('image');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_transactions');
    }
};
