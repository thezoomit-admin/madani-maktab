<?php

namespace App\Http\Controllers\Report;

use App\Enums\ArabicMonth;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\HijriMonth;
use Illuminate\Http\Request;

class ExpenseReportController extends Controller
{
    public function getArabicMonthWiseExpenseReport()
    {
        $expenses = Expense::with('category')->get();

        // Arabic month names from enum
        $arabicMonths = ArabicMonth::values(); 
        $monthNames = array_values($arabicMonths); // Just the names in order

        $report = [];

        foreach ($expenses as $expense) {
            $categoryName = $expense->category->name;

            // Find Hijri month based on expense.created_at using date range
            $hijri = HijriMonth::whereDate('start_date', '<=', $expense->created_at)
                ->whereDate('end_date', '>=', $expense->created_at)
                ->first();

            if (!$hijri) {
                continue;
            }

            $monthId = $hijri->month;
            $monthName = $arabicMonths[$monthId] ?? 'Unknown';

            // Initialize category row with all 12 months set to 0
            if (!isset($report[$categoryName])) {
                $report[$categoryName] = array_fill_keys($monthNames, 0);
            }

            // Add expense to corresponding month
            $report[$categoryName][$monthName] += $expense->total_amount;
        }

        // Build final table-style array
        $final = [];

        // Header row
        $headerRow = array_merge(['খাত/মাস'], $monthNames);
        $final[] = $headerRow;

        // Total row initialization
        $totalRow = array_fill_keys($monthNames, 0);

        // Data rows
        foreach ($report as $category => $monthValues) {
            // Sum each month's total
            foreach ($monthNames as $monthName) {
                $totalRow[$monthName] += $monthValues[$monthName];
            }

            // Add data row
            $final[] = array_merge([$category], array_values($monthValues));
        }

        // Add total row
        $final[] = array_merge(['মোট'], array_values($totalRow));

        return response()->json($final);
    }


}
