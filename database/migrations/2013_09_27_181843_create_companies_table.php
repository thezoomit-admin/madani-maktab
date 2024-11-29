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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();  
            $table->string('name');  
            $table->string('website')->nullable(); 
            $table->text('address')->nullable(); 
            $table->string('logo')->nullable();  
            $table->string('primary_color')->nullable(); 
            $table->string('secondary_color')->nullable();  
            $table->date('founded_date')->nullable();  
            $table->boolean('is_active')->default(true);
         
            $table->foreignId('category_id')->constrained('company_categories')->onDelete('cascade');
        
            $table->timestamps(); 
            $table->softDeletes();  
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
