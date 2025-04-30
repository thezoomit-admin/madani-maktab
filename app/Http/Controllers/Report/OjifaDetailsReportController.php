<?php

namespace App\Http\Controllers\Report;

use App\Enums\ArabicMonth;
use App\Enums\FeeType;
use App\Http\Controllers\Controller;
use App\Models\HijriMonth;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder; 
 

class OjifaDetailsReportController extends Controller
{
    public function OjifaReport(Request $request)
    { 
        $baseQuery = $this->getBaseQuery($request); 
        $monthsData = $this->getHijriMonths();
        $hijriMonths = $monthsData['hijri_months'];
        $monthKeys = $monthsData['month_keys']; 
        $totalData = $this->buildTotalSection($baseQuery, $monthKeys);
        $generalData = $this->buildGeneralSection($baseQuery, $monthKeys);
        $halfData = $this->buildHalfSection($baseQuery, $monthKeys);
        $overallData = $this->buildOverallSection($baseQuery, $monthKeys); 

        // Add header row only to the total section
        $headerRow = ["1" => ''];
        foreach ($monthKeys as $monthId) {
            $headerRow[$monthId] = $hijriMonths[$monthId];
        }

        $datas = [
            'total' => array_merge([$headerRow], $totalData), // Header row included in total
            'general' => $generalData, // No header row
            'half' => $halfData, // No header row
            'overall' => $overallData, // No header row
        ];

        return success_response($datas);
    }

    public function getBaseQuery(Request $request)
    {
        $year = $request->input('year');
        $reason = $request->input('reason');
        $feeType = $request->input('fee_type');

        $query = Payment::query();

        if ($year) {
            $query->where('year', $year);
        }
        if ($reason) {
            $query->where('reason', $reason);
        }
        if ($feeType !== null) {  
            $query->where('fee_type', $feeType);
        }

        return $query;
    }

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
            'hijri_month_mapping' => $hijriMonthRecords,
        ];
    } 

    private function buildTotalSection(Builder $baseQuery, array $monthKeys): array
    {
        $totalData = [];
        $feeTypes = FeeType::values(); 

        $totalRow = ["1" => 'মোট তালিবে ইলম'];
        foreach ($monthKeys as $monthValue) {
            $totalStudents = (clone $baseQuery)
                ->whereIn('hijri_month_id', function ($query) use ($monthValue) {
                    $query->select('id')
                          ->from('hijri_months')
                          ->where('month', $monthValue);
                })
                ->distinct('student_id')
                ->count('student_id');
            $totalRow[(string)$monthValue] = $totalStudents > 0 ? $totalStudents : '-';
        }
        $totalData[] = $totalRow; 

        foreach ($feeTypes as $feeTypeId => $feeTypeLabel) {
            $row = ["1" => $feeTypeLabel];
            foreach ($monthKeys as $monthValue) {
                $studentCount = (clone $baseQuery)
                    ->where('fee_type', $feeTypeId)
                    ->whereIn('hijri_month_id', function ($query) use ($monthValue) {
                        $query->select('id')
                              ->from('hijri_months')
                              ->where('month', $monthValue);
                    })
                    ->distinct('student_id')
                    ->count('student_id');
                $row[(string)$monthValue] = $studentCount > 0 ? $studentCount : '-';
            }
            $totalData[] = $row;
        }

        return $totalData;
    }

    private function buildGeneralSection(Builder $baseQuery, array $monthKeys): array
    {
        $generalData = [];
        $generalFeeTypes = [
            FeeType::General,
            FeeType::HalfButThisMonthGeneral,
            FeeType::GuestButThisMonthGeneral,
        ];  

        $generalCollectorsRow = ["1" => 'সাধারণ আদায়কারী'];
        foreach ($monthKeys as $monthValue) {
            $count = (clone $baseQuery)
                ->whereIn('fee_type', $generalFeeTypes)
                ->whereIn('hijri_month_id', function ($query) use ($monthValue) {
                    $query->select('id')
                          ->from('hijri_months')
                          ->where('month', $monthValue);
                })
                ->distinct('student_id')
                ->count('student_id');
            $generalCollectorsRow[(string)$monthValue] = $count > 0 ? $count : '-';
        }
        $generalData[] = $generalCollectorsRow; 

        $generalCollectedRow = ["1" => 'সাধারণ আদায় করেছে'];
        foreach ($monthKeys as $monthValue) {
            $count = (clone $baseQuery)
                ->whereIn('fee_type', $generalFeeTypes)
                ->whereIn('hijri_month_id', function ($query) use ($monthValue) {
                    $query->select('id')
                          ->from('hijri_months')
                          ->where('month', $monthValue);
                })
                ->where('due', 0)
                ->distinct('student_id')
                ->count('student_id');
            $generalCollectedRow[(string)$monthValue] = $count > 0 ? $count : '-';
        }
        $generalData[] = $generalCollectedRow; 

        $generalPendingRow = ["1" => 'সাধারণ বাকি'];
        foreach ($monthKeys as $monthValue) {
            $count = (clone $baseQuery)
                ->whereIn('fee_type', $generalFeeTypes)
                ->whereIn('hijri_month_id', function ($query) use ($monthValue) {
                    $query->select('id')
                          ->from('hijri_months')
                          ->where('month', $monthValue);
                })
                ->where('due', '>', 0)
                ->distinct('student_id')
                ->count('student_id');
            $generalPendingRow[(string)$monthValue] = $count > 0 ? $count : '-';
        }
        $generalData[] = $generalPendingRow; 

        $generalToBeCollectedRow = ["1" => 'সাধারণ আদায় হবে'];
        foreach ($monthKeys as $monthValue) {
            $total = (clone $baseQuery)
                ->whereIn('fee_type', $generalFeeTypes)
                ->whereIn('hijri_month_id', function ($query) use ($monthValue) {
                    $query->select('id')
                          ->from('hijri_months')
                          ->where('month', $monthValue);
                })
                ->sum('amount');
            $generalToBeCollectedRow[(string)$monthValue] = $total > 0 ? $total : '-';
        }
        $generalData[] = $generalToBeCollectedRow; 

        $generalTotalCollectedRow = ["1" => 'সাধারণ আদায় মোট'];
        foreach ($monthKeys as $monthValue) {
            $total = (clone $baseQuery)
                ->whereIn('fee_type', $generalFeeTypes)
                ->whereIn('hijri_month_id', function ($query) use ($monthValue) {
                    $query->select('id')
                          ->from('hijri_months')
                          ->where('month', $monthValue);
                })
                ->sum('paid');
            $generalTotalCollectedRow[(string)$monthValue] = $total > 0 ? $total : '-';
        }
        $generalData[] = $generalTotalCollectedRow; 

        $generalTotalPendingRow = ["1" => 'সাধারণ বাকি মোট'];
        foreach ($monthKeys as $monthValue) {
            $total = (clone $baseQuery)
                ->whereIn('fee_type', $generalFeeTypes)
                ->whereIn('hijri_month_id', function ($query) use ($monthValue) {
                    $query->select('id')
                          ->from('hijri_months')
                          ->where('month', $monthValue);
                })
                ->sum('due');
            $generalTotalPendingRow[(string)$monthValue] = $total > 0 ? $total : '-';
        }
        $generalData[] = $generalTotalPendingRow; 

        return $generalData;
    } 

    private function buildHalfSection(Builder $baseQuery, array $monthKeys): array
    {
        $halfData = [];
        $halfFeeTypes = [FeeType::Half]; 

        $halfCollectorsRow = ["1" => 'আংশিক আদায়কারী'];
        foreach ($monthKeys as $monthValue) {
            $count = (clone $baseQuery)
                ->whereIn('fee_type', $halfFeeTypes)
                ->whereIn('hijri_month_id', function ($query) use ($monthValue) {
                    $query->select('id')
                          ->from('hijri_months')
                          ->where('month', $monthValue);
                })
                ->distinct('student_id')
                ->count('student_id');
            $halfCollectorsRow[(string)$monthValue] = $count > 0 ? $count : '-';
        }
        $halfData[] = $halfCollectorsRow; 

        $halfCollectedRow = ["1" => 'আংশিক আদায় করেছে'];
        foreach ($monthKeys as $monthValue) {
            $count = (clone $baseQuery)
                ->whereIn('fee_type', $halfFeeTypes)
                ->whereIn('hijri_month_id', function ($query) use ($monthValue) {
                    $query->select('id')
                          ->from('hijri_months')
                          ->where('month', $monthValue);
                })
                ->where('due', 0)
                ->distinct('student_id')
                ->count('student_id');
            $halfCollectedRow[(string)$monthValue] = $count > 0 ? $count : '-';
        }
        $halfData[] = $halfCollectedRow; 

        $halfPendingRow = ["1" => 'আংশিক বাকি'];
        foreach ($monthKeys as $monthValue) {
            $count = (clone $baseQuery)
                ->whereIn('fee_type', $halfFeeTypes)
                ->whereIn('hijri_month_id', function ($query) use ($monthValue) {
                    $query->select('id')
                          ->from('hijri_months')
                          ->where('month', $monthValue);
                })
                ->where('due', '>', 0)
                ->distinct('student_id')
                ->count('student_id');
            $halfPendingRow[(string)$monthValue] = $count > 0 ? $count : '-';
        }
        $halfData[] = $halfPendingRow; 

        $halfToBeCollectedRow = ["1" => 'আংশিক আদায় হবে'];
        foreach ($monthKeys as $monthValue) {
            $total = (clone $baseQuery)
                ->whereIn('fee_type', $halfFeeTypes)
                ->whereIn('hijri_month_id', function ($query) use ($monthValue) {
                    $query->select('id')
                          ->from('hijri_months')
                          ->where('month', $monthValue);
                })
                ->sum('amount');
            $halfToBeCollectedRow[(string)$monthValue] = $total > 0 ? $total : '-';
        }
        $halfData[] = $halfToBeCollectedRow;  

        $halfTotalCollectedRow = ["1" => 'আংশিক আদায় মোট'];
        foreach ($monthKeys as $monthValue) {
            $total = (clone $baseQuery)
                ->whereIn('fee_type', $halfFeeTypes)
                ->whereIn('hijri_month_id', function ($query) use ($monthValue) {
                    $query->select('id')
                          ->from('hijri_months')
                          ->where('month', $monthValue);
                })
                ->sum('paid');
            $halfTotalCollectedRow[(string)$monthValue] = $total > 0 ? $total : '-';
        }
        $halfData[] = $halfTotalCollectedRow; 

        $halfTotalPendingRow = ["1" => 'আংশিক বাকি মোট'];
        foreach ($monthKeys as $monthValue) {
            $total = (clone $baseQuery)
                ->whereIn('fee_type', $halfFeeTypes)
                ->whereIn('hijri_month_id', function ($query) use ($monthValue) {
                    $query->select('id')
                          ->from('hijri_months')
                          ->where('month', $monthValue);
                })
                ->sum('due');
            $halfTotalPendingRow[(string)$monthValue] = $total > 0 ? $total : '-';
        }
        $halfData[] = $halfTotalPendingRow;

        return $halfData;
    }

    private function buildOverallSection(Builder $baseQuery, array $monthKeys): array
    {
        $overallData = [];
        $overallFeeTypes = [
            FeeType::General,
            FeeType::Half,
            FeeType::HalfButThisMonthGeneral,
            FeeType::GuestButThisMonthGeneral,
        ];

        $overallToBeCollectedRow = ["1" => 'মোট আদায় হবে'];
        foreach ($monthKeys as $monthValue) {
            $total = (clone $baseQuery)
                ->whereIn('fee_type', $overallFeeTypes)
                ->whereIn('hijri_month_id', function ($query) use ($monthValue) {
                    $query->select('id')
                          ->from('hijri_months')
                          ->where('month', $monthValue);
                })
                ->sum('amount');
            $overallToBeCollectedRow[(string)$monthValue] = $total > 0 ? $total : '-';
        }
        $overallData[] = $overallToBeCollectedRow;

        $overallTotalCollectedRow = ["1" => 'মোট আদায়'];
        foreach ($monthKeys as $monthValue) {
            $total = (clone $baseQuery)
                ->whereIn('fee_type', $overallFeeTypes)
                ->whereIn('hijri_month_id', function ($query) use ($monthValue) {
                    $query->select('id')
                          ->from('hijri_months')
                          ->where('month', $monthValue);
                })
                ->sum('paid');
            $overallTotalCollectedRow[(string)$monthValue] = $total > 0 ? $total : '-';
        }
        $overallData[] = $overallTotalCollectedRow;  

        $overallCollectorsRow = ["1" => 'আদায়কারী'];
        foreach ($monthKeys as $monthValue) {
            $count = (clone $baseQuery)
                ->whereIn('fee_type', $overallFeeTypes)
                ->whereIn('hijri_month_id', function ($query) use ($monthValue) {
                    $query->select('id')
                          ->from('hijri_months')
                          ->where('month', $monthValue);
                })
                ->distinct('student_id')
                ->count('student_id');
            $overallCollectorsRow[(string)$monthValue] = $count > 0 ? $count : '-';
        }
        $overallData[] = $overallCollectorsRow;

        $overallCollectedRow = ["1" => 'আদায় করেছে'];
        foreach ($monthKeys as $monthValue) {
            $count = (clone $baseQuery)
                ->whereIn('fee_type', $overallFeeTypes)
                ->whereIn('hijri_month_id', function ($query) use ($monthValue) {
                    $query->select('id')
                          ->from('hijri_months')
                          ->where('month', $monthValue);
                })
                ->where('due', 0)
                ->distinct('student_id')
                ->count('student_id');
            $overallCollectedRow[(string)$monthValue] = $count > 0 ? $count : '-';
        }
        $overallData[] = $overallCollectedRow; 

        $overallPendingRow = ["1" => 'আদায়কারী বাকি'];
        foreach ($monthKeys as $monthValue) {
            $count = (clone $baseQuery)
                ->whereIn('fee_type', $overallFeeTypes)
                ->whereIn('hijri_month_id', function ($query) use ($monthValue) {
                    $query->select('id')
                          ->from('hijri_months')
                          ->where('month', $monthValue);
                })
                ->where('due', '>', 0)
                ->distinct('student_id')
                ->count('student_id');
            $overallPendingRow[(string)$monthValue] = $count > 0 ? $count : '-';
        }
        $overallData[] = $overallPendingRow; 

        $overallTotalPendingRow = ["1" => 'মোট আদায় বাকি'];
        foreach ($monthKeys as $monthValue) {
            $total = (clone $baseQuery)
                ->whereIn('fee_type', $overallFeeTypes)
                ->whereIn('hijri_month_id', function ($query) use ($monthValue) {
                    $query->select('id')
                          ->from('hijri_months')
                          ->where('month', $monthValue);
                })
                ->sum('due');
            $overallTotalPendingRow[(string)$monthValue] = $total > 0 ? $total : '-';
        }
        $overallData[] = $overallTotalPendingRow; 

        return $overallData;
    }   
}