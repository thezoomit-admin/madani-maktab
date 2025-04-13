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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
        
            $table->foreignId('student_id')
                ->constrained('students')
                ->onDelete('cascade');
        
            $table->foreignId('hijri_month_id')
                ->constrained('hijri_months')
                ->onDelete('cascade');
        
            $table->string('reason');
            $table->string('fee_type')->nullable();
        
            $table->decimal('amount', 10, 2)->default(0);
            $table->decimal('paid', 10, 2)->default(0);
            $table->decimal('due', 10, 2)->default(0);
        
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
        
            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
