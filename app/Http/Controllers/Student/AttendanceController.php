<?php

namespace App\Http\Controllers\Student;

use App\Helpers\HijriDateService;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\HijriMonth;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    public function attendance(Request $request, $user_id = null)
    {
        $user_id = $user_id ?? Auth::id();

        if($request->month_id){
            $month = HijriMonth::find($request->month_id); 
        }else{
            $month = HijriMonth::getActiveMonth();
        } 

        $startDate = Carbon::parse($month->start_date)->startOfDay();
        $endDate = Carbon::parse($month->end_date)->endOfDay();


        $attendances = Attendance::where('user_id', $user_id)
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('in_time', [$startDate, $endDate])
                    ->orWhereBetween('out_time', [$startDate, $endDate]);
            })
            ->orderBy('in_time', 'asc')
            ->get();

        $hijriService = new HijriDateService();

        $attendance = $attendances->map(function ($record) use ($hijriService) {
            $inTime = Carbon::parse($record->in_time);
            $outTime = $record->out_time ? Carbon::parse($record->out_time) : null;

            return [
                'id'        => $record->id,
                'in_date'   => $hijriService->getHijri($inTime->toDateString()),
                'in_time'   => $inTime->format('H:i:s'),
                'out_date'  => $outTime ? $hijriService->getHijri($outTime->toDateString()) : null,
                'out_time'  => $outTime ? $outTime->format('H:i:s') : null,
                'comment'   => $record->comment,
            ];
        });

        return success_response($attendance->reverse()->values());
    } 

    public function outReason(Request $request)
    { 
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:attendances,id',
            'comment' => 'required|string|max:1000',
        ], [
            'id.required' => 'অ্যাটেনডেন্স আইডি অবশ্যই দিতে হবে।',
            'id.exists' => 'প্রদত্ত আইডি সঠিক নয় বা খুঁজে পাওয়া যায়নি।',
            'comment.required' => 'মন্তব্য লিখতে হবে।',
            'comment.string' => 'মন্তব্য অবশ্যই টেক্সট হতে হবে।',
            'comment.max' => 'মন্তব্য ১০০০ অক্ষরের বেশি হতে পারবে না।',
        ]);

        if ($validator->fails()) {
            return error_response(null, 422, $validator->errors());
        }
 
        $attendance = Attendance::find($request->id);
        $attendance->comment = $request->comment;
        $attendance->comment_by = auth()->id();  
        $attendance->save(); 
        return success_response(null, "মন্তব্য সফলভাবে যুক্ত করা হয়েছে।");
    }

}
