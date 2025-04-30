<?php

namespace App\Http\Controllers\Report;

use App\Enums\ArabicMonth;
use App\Enums\FeeType;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class OjifaDetailsReportController extends Controller
{
    public function ojifaReport(){
        $hijriMonths = ArabicMonth::values();
        $monthKeys = array_keys($hijriMonths);
 
        unset($hijriMonths[ArabicMonth::RAMADAN]);
        $monthKeys = array_filter($monthKeys, fn($key) => $key != ArabicMonth::RAMADAN);
 
        $feeTypes = FeeType::values();
        $feeTypeKeys = array_keys($feeTypes); 
        $tableData = []; 
        $totalRow = [
            'fee_type' => 'মোট তালিবে ইলম',  
            'counts' => [],  
        ];

        foreach ($monthKeys as $monthId) { 
            $totalStudents = Payment::where('hijri_month_id', $monthId)
                ->distinct('student_id')
                ->count('student_id');
            $totalRow['counts'][$monthId] = $totalStudents ?: 0;
        }
 
        $tableData[] = $totalRow; 
        foreach ($feeTypes as $feeTypeId => $feeTypeLabel) {
            $row = [
                'fee_type' => $feeTypeLabel,  
                'counts' => [],  
            ];

            foreach ($monthKeys as $monthId) { 
                $studentCount = Payment::where('fee_type', $feeTypeId)
                    ->where('hijri_month_id', $monthId)
                    ->distinct('student_id')
                    ->count('student_id');

                $row['counts'][$monthId] = $studentCount ?: 0;
            }

            $tableData[] = $row;
        }
 
        return response()->json([
            'hijri_months' => $hijriMonths,
            'table_data' => $tableData,
        ]);
    }
}
