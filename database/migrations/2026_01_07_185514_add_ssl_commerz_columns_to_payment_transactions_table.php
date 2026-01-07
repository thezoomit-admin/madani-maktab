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
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->string('transaction_id')->nullable()->after('payment_method_id');
            $table->string('val_id')->nullable()->after('transaction_id');
            $table->string('bank_tran_id')->nullable()->after('val_id');
            $table->string('status')->default('Pending')->after('amount');
            $table->string('card_type')->nullable()->after('image');
            $table->string('card_no')->nullable()->after('card_type');
            $table->string('currency')->default('BDT')->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropColumn(['transaction_id', 'val_id', 'bank_tran_id', 'status', 'card_type', 'card_no', 'currency']);
        });
    }
};
