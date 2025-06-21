<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{ 
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('reg_id')->nullable();
            $table->dateTime('in_time');
            $table->string('in_access_id');
            $table->dateTime('out_time')->nullable(); 
            $table->string('out_access_id')->nullable();
            $table->text('comment')->nullable();
            $table->foreignId('comment_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }  

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
