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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->integer('expenses_no')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('expense_category_id')->constrained('expense_categories')->onDelete('cascade');
            $table->foreignId('expense_sub_category_id')->constrained('expense_sub_categories')->onDelete('cascade');
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->onDelete('set null');
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->onDelete('cascade'); 
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
 
            $table->decimal('amount', 10, 2)->nullable();
            $table->decimal('total_amount', 10, 2);
            $table->text('description')->nullable();
            $table->string('measurement')->nullable();
            $table->foreignId('measurment_unit_id')->nullable()->constrained('measurment_units')->onDelete('set null');
            $table->string('image')->nullable();
            $table->string('voucher_no')->nullable();

            // Approval
            $table->boolean('is_approved')->default(false); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
