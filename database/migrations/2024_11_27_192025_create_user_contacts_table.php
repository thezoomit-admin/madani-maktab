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
        Schema::create('user_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(); 
            $table->string('name')->nullable()->comment("if customer type is company");
            $table->string('office_phone', 15)->nullable();
            $table->string('personal_phone', 15)->nullable();
            $table->string('office_email', 45)->nullable();
            $table->string('personal_email', 45)->nullable();
            $table->string('imo_number', 15)->nullable();
            $table->string('facebook_id', 100)->nullable(); 
            $table->string('emergency_contact_number', 15)->nullable();
            $table->string('emergency_contact_person', 45)->nullable(); 
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_contacts');
    }
};
