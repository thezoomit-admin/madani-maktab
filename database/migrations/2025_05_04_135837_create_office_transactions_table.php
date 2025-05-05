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
            $table->tinyInteger('type')->comment('1 = Deposit (Joma), 2 = Withdraw (Uttolon)');
            $table->foreignId('hijri_month_id')->constrained('hijri_months');
            $table->foreignId('payment_method_id')->constrained('payment_methods');
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('image')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->foreignId('created_by')->constrained('users');
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
