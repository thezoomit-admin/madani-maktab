<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;

class TotalIncomeReportController extends Controller
{
    public function index(Request $request)
{
    try { 
        $totalIncome = PaymentTransaction::where('is_approved', true)->sum('amount');
 
        $categoryExpenses = Expense::with('category')
            ->selectRaw('expense_category_id, SUM(total_amount) as total')
            ->groupBy('expense_category_id')
            ->get()
            ->map(function ($item) {
                return [
                    'category' => optional($item->category)->name ?? 'Unknown',
                    'total_expense' => $item->total,
                ];
            });
 
        $totalExpense = $categoryExpenses->sum('total_expense');
 
        $profitOrLoss = $totalIncome - $totalExpense;
 
        return success_response([
            'category_wise_expense' => $categoryExpenses,
            'total_expense' => $totalExpense,
            'total_income' => $totalIncome,
            'profit_or_loss' => $profitOrLoss,
        ], 'Income vs Expense report generated successfully.');
    } catch (\Exception $e) {
        return error_response($e->getMessage(), 500, 'Failed to generate report.');
    }
}

}
