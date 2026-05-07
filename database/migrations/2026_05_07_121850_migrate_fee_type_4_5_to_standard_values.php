<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            // ── enroles ──────────────────────────────────────────────
            DB::table('enroles')->where('fee_type', 4)->update(['fee_type' => 2]);
            DB::table('enroles')->where('fee_type', 5)->update(['fee_type' => 3]);

            // ── payments: HalfButThisMonthGeneral (4) ────────────────
            $firstHalf = DB::table('payments')
                ->where('fee_type', 4)
                ->groupBy('student_id')
                ->pluck(DB::raw('MIN(id)'));

            DB::table('payments')->whereIn('id', $firstHalf)->update(['fee_type' => 1]);
            DB::table('payments')->where('fee_type', 4)->update(['fee_type' => 2]);

            // ── payments: GuestButThisMonthGeneral (5) ───────────────
            $firstGuest = DB::table('payments')
                ->where('fee_type', 5)
                ->groupBy('student_id')
                ->pluck(DB::raw('MIN(id)'));

            DB::table('payments')->whereIn('id', $firstGuest)->update(['fee_type' => 1]);
            DB::table('payments')->where('fee_type', 5)->update(['fee_type' => 3]);
        });
    }

    public function down(): void
    {
        // data মিশে যাওয়ায় rollback সম্ভব নয়
    }
};
