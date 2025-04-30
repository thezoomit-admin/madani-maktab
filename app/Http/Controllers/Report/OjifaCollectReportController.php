<?php

namespace App\Http\Controllers\Report;

use App\Enums\ArabicMonth;
use App\Enums\Department;
use App\Enums\MaktabSession;
use App\Http\Controllers\Controller;
use App\Models\Enrole;
use App\Models\HijriMonth;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
 

class OjifaCollectReportController extends Controller
{
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
}
