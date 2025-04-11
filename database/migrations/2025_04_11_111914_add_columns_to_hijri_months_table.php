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
        Schema::table('hijri_months', function (Blueprint $table) {
            $table->string('year')->after('id');  
            $table->date('start_date')->after('month');  
            $table->date('end_date')->nullable()->after('start_date'); 
            $table->boolean('is_active')->default(true)->after('end_date'); 
            $table->foreignId('created_by')->constrained('users')->after('is_active');  
            $table->foreignId('updated_by')->constrained('users')->after('created_by');
            $table->unique(['year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hijri_months', function (Blueprint $table) {
            $table->dropColumn(['year', 'start_date', 'end_date', 'is_active', 'created_by', 'updated_by']);
            $table->dropUnique(['year', 'month']);
        });
    }
};
