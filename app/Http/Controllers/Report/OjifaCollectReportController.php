<?php

namespace App\Http\Controllers\Report;

use App\Enums\ArabicMonth;
use App\Enums\Department;
use App\Enums\FeeReason;
use App\Enums\FeeType;
use App\Enums\MaktabSession;
use App\Enums\KitabSession;
use App\Http\Controllers\Controller;
use App\Models\Enrole;
use App\Models\HijriMonth;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\PaginateTrait;
 

class OjifaCollectReportController extends Controller
{
    use PaginateTrait;
    private function getBaseQuery(Request $request): Builder
    {
        $year = $request->input('year',1446);

        $query = Enrole::query()->where('year',$year)
            ->join('users', 'enroles.user_id', '=', 'users.id')
            ->select('enroles.*', 'users.reg_id', 'users.name');

        return $query;
    }

    /**
     * Get Hijri months excluding Ramadan.
     */
    private function getHijriMonths(): array
    {
        // Fetch all records from the hijri_months table
        $hijriMonthRecords = HijriMonth::all()->pluck('month', 'id')->toArray();

        // Get all Arabic months from the enum
        $arabicMonthValues = ArabicMonth::values();
        $allMonthKeys = array_keys($arabicMonthValues);

        // Exclude Ramadan and prepare the list of months
        $hijriMonths = [];
        $monthKeys = [];

        foreach ($allMonthKeys as $monthValue) {
            // Skip Ramadan
            if ($monthValue == ArabicMonth::RAMADAN) {
                continue;
            }

            $hijriMonths[$monthValue] = $arabicMonthValues[$monthValue];
            $monthKeys[] = $monthValue;
        }

        return [
            'hijri_months' => $hijriMonths,
            'month_keys' => $monthKeys,
            'hijri_month_mapping' => $hijriMonthRecords, // Pass the mapping for use in queries
        ];
    }

    /**
     * Get the student payment report.
     */
    public function getStudentPaymentReport(Request $request)
    {
        // Get the base query for enroles
        $baseQuery = $this->getBaseQuery($request); 

        // Get Hijri months
        $monthsData = $this->getHijriMonths();
        $hijriMonths = $monthsData['hijri_months'];
        $monthKeys = $monthsData['month_keys'];
        $hijriMonthMapping = $monthsData['hijri_month_mapping'];

        // Get all enrole records (unique students)
        $enroles = $baseQuery->get();

        // Fetch payment data for all students, applying year filter if provided
        $paymentQuery = Payment::query()
            ->whereIn('enrole_id', $enroles->pluck('id'));

     

        $payments = $paymentQuery->get()->groupBy('enrole_id');

        $reportData = [];

        // Create the header row
        $headerRow = [
            'class' => 'শ্রেণী',
            'reg_id' => 'পরিচিতি নং',
            'student_name' => 'তালিবে ইলমের নাম',
            'total_months' => 'মোট',
            'admission_fee' => 'দাখেলা',
        ];

        // Add Arabic month names as column headers
        foreach ($monthKeys as $monthId) {
            $headerRow[$hijriMonths[$monthId]] = $hijriMonths[$monthId];
        }

        $reportData[] = $headerRow;

        // Process each enrole record (each student)
        foreach ($enroles as $enrole) {
            // Get class (শ্রেণী) from session via MaktabSession enum
            $sessionId = $enrole->session;
            $className = MaktabSession::values()[$sessionId] ?? 'অজানা শ্রেণি';

            // Get department (মক্তব or কিতাব)
            $departmentId = $enrole->department_id;
            $departmentSort = $departmentId == Department::Maktab ? 1 : 2; // Maktab first, then Kitab

            // Get the student's payments
            $studentPayments = $payments[$enrole->id] ?? collect([]);

            // Count total months with payment data where reason = 2
            $totalMonths = $studentPayments
                ->where('reason', 2)
                ->count();

            // Calculate admission fee (ভর্তি ফি) where reason = 1
            $admissionFee = $studentPayments
                ->where('reason', 1)
                ->sum('amount');

            // Prepare row data
            $row = [
                'class' => $className,
                'department_sort' => $departmentSort,
                'session_id' => $sessionId,
                'reg_id' => $enrole->reg_id,
                'student_name' => $enrole->name,
                'total_months' => $totalMonths,
                'admission_fee' => $admissionFee,
            ];

            // Add payment status for each month
            foreach ($monthKeys as $monthId) {
                $monthName = $hijriMonths[$monthId];
                // Find all hijri_month_ids that correspond to this month value
                $matchingHijriMonthIds = array_keys($hijriMonthMapping, $monthId);
                $payment = null;
                if (!empty($matchingHijriMonthIds)) {
                    $payment = $studentPayments->first(function ($payment) use ($matchingHijriMonthIds) {
                        return in_array($payment->hijri_month_id, $matchingHijriMonthIds);
                    });
                }

                if ($payment) {
                    $status = $payment->due == 0 ? '✓' : '✗';
                    $row[$monthName] = [
                        'text' => $status,
                        'payment_id' => $payment->id,
                    ];
                } else {
                    $row[$monthName] = '-';
                }
            }

            $reportData[] = $row;
        }

        // Sort the data: First by department (Maktab, then Kitab), then by session (প্রথম শ্রেণি to পঞ্চম শ্রেণি)
        // Skip the header row (index 0) during sorting
        $header = array_shift($reportData);
        usort($reportData, function ($a, $b) {
            // Sort by department first
            if ($a['department_sort'] !== $b['department_sort']) {
                return $a['department_sort'] <=> $b['department_sort'];
            }
            // Within the same department, sort by session_id (class)
            return $a['session_id'] <=> $b['session_id'];
        });

        // Add the header row back at the beginning
        array_unshift($reportData, $header);

        // Remove sorting keys from the final output
        $reportData = array_map(function ($row) {
            unset($row['department_sort']);
            unset($row['session_id']);
            return $row;
        }, $reportData);

        // Prepare the final response
        return response()->json([
            'data' => $reportData,
        ]);
    } 

    public function getStudentPaymentReportV2(Request $request){
        $year = $request->input('year');

        $query = Payment::query()
            ->select([
                'payments.id',
                'payments.user_id',
                'payments.enrole_id',
                'payments.hijri_month_id',
                'payments.reason',
                'payments.year',
                'payments.amount',
                'payments.paid',
                'payments.due',
                'payments.fee_type',
                'users.reg_id',
                'users.name as student_name',
                'enroles.department_id',
                'enroles.session',
                'hijri_months.month as hijri_month_value'
            ])
            ->join('users', 'users.id', '=', 'payments.user_id')
            ->join('enroles', 'enroles.id', '=', 'payments.enrole_id')
            ->join('hijri_months', 'hijri_months.id', '=', 'payments.hijri_month_id');

        if ($request->filled('year')) {
            $query->where('payments.year', $year);
        }

        if ($request->filled('department_id')) {
            $query->where('enroles.department_id', $request->input('department_id'));
        }

        if ($request->filled('session')) {
            $query->where('enroles.session', $request->input('session'));
        }

        if ($request->filled('reason')) {
            $query->where('payments.reason', $request->input('reason'));
        }

        if ($request->filled('fee_type')) {
            $query->where('enroles.fee_type', $request->input('fee_type'));
        }

        if ($request->filled('month')) {
            $month = $request->input('month');
            $query->where('payments.hijri_month_id', $request->input('month'));
        }

        $query->orderBy('payments.id', 'asc');

        if ($request->filled('status')) {
            $status = $request->input('status');
            $query->where(function ($q) use ($status) {
                if ($status === 'paid') {
                    $q->where('payments.due', 0);
                } elseif ($status === 'unpaid') {
                    $q->where('payments.due', '>', 0);
                }
            });
        }

        if ($request->filled('reg_id')) {
            $query->where('users.reg_id', $request->input('reg_id'));
        }

        if ($request->filled('student_name')) {
            $query->where('users.name', 'like', '%' . $request->input('student_name') . '%');
        }

        $summaryQuery = clone $query;
        $paginated = $this->paginateQuery(clone $query, $request);
        $payments = collect($paginated['data']);

        $departmentLabels = Department::values();
        $maktabSessions = MaktabSession::values();
        $kitabSessions = KitabSession::values();
        $feeTypeLabels = FeeType::values();
        $reasonLabels = FeeReason::values();
        $arabicMonths = ArabicMonth::values();

        $transform = function ($payment) use ($departmentLabels, $maktabSessions, $kitabSessions, $feeTypeLabels, $reasonLabels, $arabicMonths) {
            $departmentId = (int) $payment->department_id;
            $sessionId = (int) $payment->session;
            $status = $payment->due == 0 ? 'paid' : ($payment->paid > 0 ? 'partial' : 'unpaid');

            $sessionLabel = $departmentId === Department::Maktab
                ? ($maktabSessions[$sessionId] ?? 'অজানা শ্রেণি')
                : ($kitabSessions[$sessionId] ?? 'অজানা শ্রেণি');

            return [
                'student_name' => $payment->student_name,
                'reg_id' => $payment->reg_id,
                'month' => hijri_month_name($payment->hijri_month_id),
                'reason_label' => $reasonLabels[$payment->reason] ?? '-',
                'fee_type_label' => $payment->fee_type ? ($feeTypeLabels[$payment->fee_type] ?? '-') : null,
                'amount' => (float) $payment->amount,
                'paid' => (float) $payment->paid,
                'due' => (float) $payment->due,
                'status' => $status,
                'payment_id' => $payment->id,
                'department_id' => $departmentId,
                'department' => $departmentLabels[$departmentId] ?? '-',
                'session' => $sessionLabel,
                'month_id' => (int) $payment->hijri_month_value,
                'reason' => $payment->reason,
                'fee_type' => $payment->fee_type,
                'year' => $payment->year,
            ];
        };

        $rows = $payments->map($transform)->values();
        $paginated['data'] = $rows;

        $allPayments = $summaryQuery->get();
        $allRows = $allPayments->map($transform);

        $summary = [
            'total_students' => $allPayments->pluck('user_id')->unique()->count(),
            'total_records' => $allPayments->count(),
            'total_amount' => (float) $allPayments->sum('amount'),
            'total_paid' => (float) $allPayments->sum('paid'),
            'total_due' => (float) $allPayments->sum('due'),
            'by_status' => [
                'paid' => $allRows->where('status', 'paid')->count(),
                'partial' => $allRows->where('status', 'partial')->count(),
                'unpaid' => $allRows->where('status', 'unpaid')->count(),
            ],
        ];

        return success_response([
            'filters' => [
                'year' => $year,
                'department_id' => $request->input('department_id'),
                'session' => $request->input('session'),
                'reason' => $request->input('reason'),
                'month' => $request->input('month'),
                'status' => $request->input('status'),
                'reg_id' => $request->input('reg_id'),
                'student_name' => $request->input('student_name'),
                'fee_type' => $request->input('fee_type'),
            ],
            'summary' => $summary,
            'data' => $paginated['data'],
            'meta' => $paginated['meta'],
        ]);
    }
}
