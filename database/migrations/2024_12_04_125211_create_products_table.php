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
        Schema::create('products', function (Blueprint $table) { 
            $table->id(); 
            $table->string('name'); 
            $table->string('slug')->unique(); 
            $table->text('description')->nullable();
            $table->decimal('regular_price', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('sell_price', 10, 2);
            $table->foreignId('category_id')->constrained('product_categories')->onDelete('cascade');
            
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('status')->default(1)->comment("1=Active, 0= UnActive");
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
