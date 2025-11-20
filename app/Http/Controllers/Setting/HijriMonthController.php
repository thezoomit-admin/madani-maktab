<?php

namespace App\Http\Controllers\Setting;

use App\Enums\ArabicMonth;
use App\Http\Controllers\Controller;
use App\Models\HijriMonth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class HijriMonthController extends Controller
{
    public function month_list()
    {
        $values = ArabicMonth::values();

        $list = [];
        foreach ($values as $key => $name) {
            $list[] = [
                'id' => (int) $key,
                'name' => $name,
            ];
        }

        return $list;
    } 

    public function year_list(Request $request)
    {
        $keyword = $request->input('keyword');
    
        $query = HijriMonth::query()
            ->select('year')
            ->distinct();
    
        if (!empty($keyword)) {
            $query->where('year', 'like', '%' . $keyword . '%');
        }
    
        $years = $query->orderByDesc('year')
            ->limit(10)
            ->pluck('year');
    
        return success_response($years);
    }
    

    public function index(Request $request)
    {
        $query = HijriMonth::query()->latest();

        if ($request->filled('year')) {
            $query->where('year', $request->input('year'));
        }
        if ($request->input('select2') == true) {
            $results = $query->limit(12)
                ->get()
                ->map(function ($item) use ($request) {
                    return [
                        'id' => $item->id,
                        'text' => $request->filled('year')
                            ? enum_name(ArabicMonth::class, $item->month)
                            : $item->year . '-' . enum_name(ArabicMonth::class, $item->month),
                    ];
                });

            return success_response([
                'data' => $results,
            ]);
        }

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $total = $query->count();

        $results = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'year' => $item->year,
                    'month' => enum_name(ArabicMonth::class, $item->month),
                    'start_date' => $item->start_date,
                    'end_date' => $item->end_date,
                    'is_active' => $item->is_active,
                ];
            });

        return success_response([
            'data' => $results,
            'pagination' => [
                'total' => $total,
                'per_page' => (int)$perPage,
                'current_page' => (int)$page,
                'last_page' => ceil($total / $perPage),
            ],
        ]);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year' => 'sometimes|required|string|max:10',
            'month' => 'sometimes|required|integer',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|date',
            'is_active' => 'sometimes|boolean', 
        ]);  

        if ($validator->fails()) {
            return error_response(null, 422, $validator->errors());
        }
 
        if ($request->filled('start_date')) {
            $previous = HijriMonth::orderBy('start_date', 'desc')->first();

            if ($previous && !$previous->end_date) {
                $previous->end_date = Carbon::parse($request->start_date)->subDay()->format('Y-m-d');
                $previous->save();
            }
        } 
        $input =  $request->all();
        $input['created_by'] = Auth::user()->id;
        $input['updated_by'] = Auth::user()->id; 
        
        if ($input['is_active'] == 1) {
            HijriMonth::where('is_active', 1)->update(['is_active' => 0]);
        }
        
        HijriMonth::create($input);
        return success_response(null, "Hijri Date Created Successfully");
    } 

    public function update(Request $request, $id)
    {
        $hijriMonth = HijriMonth::findOrFail($id); 
        $validator = Validator::make($request->all(), [
            'year' => 'sometimes|required|string|max:10',
            'month' => 'sometimes|required|integer',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|date',
            'is_active' => 'sometimes|boolean',
            'updated_by' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return error_response(null, 422, $validator->errors());
        }

        $originalStartDate = $hijriMonth->start_date;
        $newStartDate = $request->start_date ?? $originalStartDate;
        $newEndDate = $request->end_date ?? $hijriMonth->end_date;
 
        if ($newStartDate !== $originalStartDate) { 
            $previous = HijriMonth::where('start_date', '<', $newStartDate)
                ->orderBy('start_date', 'desc')
                ->first();

            if ($previous) {
                $previous->end_date = Carbon::parse($newStartDate)->subDay()->format('Y-m-d');
                $previous->save();
            }
 
            $next = HijriMonth::where('start_date', '>', $originalStartDate)
                ->orderBy('start_date', 'asc')
                ->first();

            if ($next && $newEndDate) {
                $next->start_date = Carbon::parse($newEndDate)->addDay()->format('Y-m-d');
                $next->save();
            }
        } 

         

        $input =  $request->all();
        $input['updated_by'] = Auth::user()->id; 

        if ($input['is_active'] == 1) {
            HijriMonth::where('is_active', 1)->update(['is_active' => 0]);
        }

        $hijriMonth->update($input); 
        return success_response(null, "Hijri Date Updated Successfully");
    } 

    public function changeStatus($id)
    {
        $hijri_month = HijriMonth::find($id); 
        if (!$hijri_month) {
            return error_response(null, 404, "এই হিজরি মাসটি খুঁজে পাওয়া যায়নি।");
        } 
        if ($hijri_month->is_active == 1) { 
            $hijri_month->is_active = 0;
            $message = "হিজরি মাসটি নিষ্ক্রিয় করা হয়েছে।";
        } else { 
            HijriMonth::where('is_active', 1)->update(['is_active' => 0]);
 
            $hijri_month->is_active = 1;
            $message = "হিজরি মাসটি সক্রিয় করা হয়েছে।";
        } 
        $hijri_month->save(); 
        return success_response(null, $message);
    }




}
