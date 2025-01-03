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
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id'); 
            $table->enum('address_type', ['permanent', 'temporary']);
            $table->string('house_or_state')->nullable();
            $table->string('village_or_area')->nullable();
            $table->string('post_office')->nullable();
            $table->string('upazila_thana')->nullable();
            $table->string('district')->nullable();
            $table->string('division')->nullable();  
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_addresses');
    }
};
