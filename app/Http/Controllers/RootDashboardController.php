<?php

namespace App\Http\Controllers;

use App\Enums\ArabicMonth;
use App\Enums\Department;
use App\Models\Admission;
use App\Models\AdmissionProgressStatus;
use App\Models\Attendance;
use App\Models\Enrole;
use App\Models\Expense;
use App\Models\HijriMonth;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\PaymentTransaction;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class RootDashboardController extends Controller
{
    public function __invoke()
    {
        $activeMonth = HijriMonth::getActiveMonth();

        return success_response([
            'student_summary'  => $this->studentSummary($activeMonth),
            'financial'        => $this->financial($activeMonth),
            'in_hand'          => $this->inHand(),
            'gateway_balances' => $this->gatewayBalances(),
            'chart'            => $this->chart($activeMonth),
            'recent_payments'  => $this->recentPayments(),
            'recent_expenses'  => $this->recentExpenses(),
        ]);
    }

    private function studentSummary($activeMonth)
    {
        $byDept = Enrole::where('status', 1)
            ->select('department_id', DB::raw('COUNT(DISTINCT student_id) as count'))
            ->groupBy('department_id')
            ->get()
            ->keyBy('department_id');

        $departments = collect(Department::values())->map(fn ($name, $id) => [
            'id'    => $id,
            'name'  => $name,
            'count' => (int) ($byDept->get($id)->count ?? 0),
        ])->values();

        $total = Student::where('status', 1)->count();

        $newThisMonth = 0;
        if ($activeMonth) {
            $newThisMonth = Student::whereBetween('created_at', [
                $activeMonth->start_date . ' 00:00:00',
                $activeMonth->end_date   . ' 23:59:59',
            ])->count();
        }

        return [
            'total'          => $total,
            'by_department'  => $departments,
            'absent_today'   => $this->absentToday(),
            'new_this_month' => $newThisMonth,
        ];
    }

    private function absentToday()
    {
        $result = [];

        foreach (Department::values() as $deptId => $deptName) {
            $students = Admission::where('department_id', $deptId)
                ->where('status', 1)
                ->get();

            $absentCount = $students->filter(function ($student) {
                $last = Attendance::where('user_id', $student->user_id)
                    ->latest('in_time')
                    ->first();
                return !$last || $last->out_time !== null;
            })->count();

            $result[] = [
                'department'   => $deptName,
                'total'        => $students->count(),
                'absent_count' => $absentCount,
            ];
        }

        return $result;
    }

    private function financial($activeMonth)
    {
        $collectedThisMonth = 0;
        $spentThisMonth     = 0;

        if ($activeMonth) {
            $collectedThisMonth = Payment::where('hijri_month_id', $activeMonth->id)
                ->sum('paid');

            $spentThisMonth = Expense::whereBetween('created_at', [
                $activeMonth->start_date . ' 00:00:00',
                $activeMonth->end_date   . ' 23:59:59',
            ])->sum('total_amount');
        }

        $totalDue = Payment::sum('due');

        return [
            'collected_this_month' => (float) $collectedThisMonth,
            'spent_this_month'     => (float) $spentThisMonth,
            'profit_loss'          => (float) ($collectedThisMonth - $spentThisMonth),
            'total_due'            => (float) $totalDue,
        ];
    }

    private function inHand()
    {
        $totals = PaymentMethod::selectRaw('
            SUM(income_in_hand)                    AS income_in_hand,
            SUM(expense_in_hand)                   AS expense_in_hand,
            SUM(income_in_hand + expense_in_hand)  AS total
        ')->first();

        return [
            'income_in_hand'  => (float) ($totals->income_in_hand  ?? 0),
            'expense_in_hand' => (float) ($totals->expense_in_hand ?? 0),
            'total'           => (float) ($totals->total           ?? 0),
        ];
    }

    private function gatewayBalances()
    {
        return PaymentMethod::select('id', 'name', 'icon', 'income_in_hand', 'expense_in_hand')
            ->get()
            ->map(fn ($m) => [
                'id'              => $m->id,
                'name'            => $m->name,
                'icon'            => $m->icon,
                'income_in_hand'  => (float) $m->income_in_hand,
                'expense_in_hand' => (float) $m->expense_in_hand,
                'total'           => (float) ($m->income_in_hand + $m->expense_in_hand),
            ]);
    }

    private function admissionPipeline()
    {
        $stats = AdmissionProgressStatus::selectRaw('
            SUM(is_registration_complete = 1)                   AS registered,
            SUM(is_interview_scheduled = 1)                     AS interview,
            SUM(is_invited_for_trial = 1)                       AS trial,
            SUM(is_admission_completed = 1)                     AS admitted,
            SUM(is_passed_interview = 0 OR is_passed_trial = 0) AS rejected
        ')->first();

        return [
            ['stage' => 'নিবন্ধন',    'count' => (int) ($stats->registered ?? 0)],
            ['stage' => 'ইন্টারভিউ',  'count' => (int) ($stats->interview  ?? 0)],
            ['stage' => 'Trial',      'count' => (int) ($stats->trial      ?? 0)],
            ['stage' => 'ভর্তি হয়েছে', 'count' => (int) ($stats->admitted   ?? 0)],
            ['stage' => 'বাতিল',      'count' => (int) ($stats->rejected   ?? 0)],
        ];
    }

    private function chart($activeMonth)
    {
        if (!$activeMonth) {
            return ['hijri_year' => null, 'months' => []];
        }

        $year = $activeMonth->year;

        // Collected per month via join — single query
        $collections = Payment::join('hijri_months', 'payments.hijri_month_id', '=', 'hijri_months.id')
            ->where('hijri_months.year', $year)
            ->groupBy('hijri_months.month')
            ->select('hijri_months.month', DB::raw('SUM(payments.paid) as total'))
            ->pluck('total', 'month');

        $hijriMonths = HijriMonth::where('year', $year)->orderBy('month')->get();

        $monthData = $hijriMonths->map(function ($m) use ($collections) {
            $spent = Expense::whereBetween('created_at', [
                $m->start_date . ' 00:00:00',
                $m->end_date   . ' 23:59:59',
            ])->sum('total_amount');

            return [
                'month'     => (int) $m->month,
                'name'      => ArabicMonth::values()[(string) $m->month] ?? ('Month ' . $m->month),
                'collected' => (float) ($collections->get((string) $m->month, 0)),
                'spent'     => (float) $spent,
            ];
        });

        return [
            'hijri_year' => $year,
            'months'     => $monthData,
        ];
    }

    private function recentPayments()
    {
        return PaymentTransaction::with([
            'user:id,name,reg_id',
            'paymentMethod:id,name,icon',
        ])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($t) => [
                'id'             => $t->id,
                'name'           => $t->user->name          ?? null,
                'reg_id'         => $t->user->reg_id        ?? null,
                'amount'         => (float) $t->amount,
                'payment_method' => $t->paymentMethod->name ?? null,
                'status'         => $t->status,
                'date'           => $t->created_at?->format('d/m/Y'),
            ]);
    }

    private function recentExpenses()
    {
        return Expense::with([
            'category:id,name',
            'paymentMethod:id,name',
        ])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($e) => [
                'id'             => $e->id,
                'category'       => $e->category->name      ?? null,
                'amount'         => (float) $e->total_amount,
                'payment_method' => $e->paymentMethod->name ?? null,
                'description'    => $e->description,
                'date'           => $e->created_at?->format('d/m/Y'),
            ]);
    }
}
