<?php

namespace App\Http\Controllers\Report;

use App\Helpers\HijriDateService;
use App\Http\Controllers\Controller;
use App\Models\HijriMonth;
use App\Models\Vendor;
use App\Models\VendorPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DueReportController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->input('year'); 

        if (!$year) {
            $activeHijriDate = HijriMonth::where('is_active', true)->first();
            if (!$activeHijriDate) {
                return error_response(null, 404, 'Active Hijri year not found.');
            }
            $year = $activeHijriDate->year;
        }

        $dates = HijriMonth::where('year', $year)
            ->selectRaw('MIN(start_date) as start_date, MAX(end_date) as end_date')
            ->first();

        if (!$dates || !$dates->start_date || !$dates->end_date) {
            return error_response(null, 404, 'No Hijri months found for the specified year.');
        }

        $startDate = $dates->start_date;
        $endDate = $dates->end_date;

        $vendors = Vendor::select('name','id')
            ->withCount([
                'expenses as total_expense' => function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate])
                        ->select(DB::raw("COALESCE(SUM(total_amount), 0)"));
                },
                'payments as total_payment' => function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate])
                        ->select(DB::raw("COALESCE(SUM(amount), 0)"));
                }
            ])
            ->get()
            ->map(function ($vendor) {
                return [
                    'id' => $vendor->id,
                    'name' => $vendor->name,
                    'total_expense' => $vendor->total_expense,
                    'total_payment' => $vendor->total_payment,
                    'total_due' => $vendor->total_expense - $vendor->total_payment,
                ];
            });

        // Calculate total due across all vendors
        $totalDue = $vendors->sum('total_due');

        return success_response([ 
            'total_due' => $totalDue,
            'vendors' => $vendors,
        ]);
    }

    public function paymentList(Request $request)
    {
        $year = $request->input('year');
        $vendorId = $request->input('vendor_id');  

        if (!$year) {
            $activeHijriDate = HijriMonth::where('is_active', true)->first();
            if (!$activeHijriDate) {
                return error_response(null, 404, 'Active Hijri year not found.');
            }
            $year = $activeHijriDate->year;
        }

        $dates = HijriMonth::where('year', $year)
            ->selectRaw('MIN(start_date) as start_date, MAX(end_date) as end_date')
            ->first();

        if (!$dates || !$dates->start_date || !$dates->end_date) {
            return error_response(null, 404, 'No Hijri months found for the specified year.');
        }

        $startDate = $dates->start_date;
        $endDate = $dates->end_date;
 
        $query = VendorPayment::whereBetween('created_at', [$startDate, $endDate])
            ->with('vendor');  
 
        if ($vendorId) {
            $query->where('vendor_id', $vendorId);
        }

        $vendorPayments = $query->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'date' => app(HijriDateService::class)->getHijri($item->created_at),
                'amount' => $item->amount,
                'vendor' => $item->vendor->name ?? null,
            ];
        });

        return success_response($vendorPayments, 'Vendor payments fetched successfully.');
    }

}
