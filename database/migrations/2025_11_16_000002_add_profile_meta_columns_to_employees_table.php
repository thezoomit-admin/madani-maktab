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
        Schema::table('employees', function (Blueprint $table) {
            $table->text('description')->nullable()->after('signature');
            $table->text('education_qualification')->nullable()->after('description');
            $table->text('previous_work_details')->nullable()->after('education_qualification');
            $table->string('maritial_status')->nullable()->after('previous_work_details');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'description',
                'education_qualification',
                'previous_work_details',
                'maritial_status',
            ]);
        });
    }
};


