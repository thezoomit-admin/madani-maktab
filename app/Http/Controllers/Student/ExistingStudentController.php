<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExistingStudentRegisterRequest;
use App\Models\Admission;
use App\Models\Enrole;
use App\Models\FeeSetting;
use App\Models\Guardian;
use App\Models\HijriMonth;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use App\Models\UserAddress;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ExistingStudentController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 20);  
            $page = $request->input('page', 1); 
            $keyword = $request->input('keyword');  

            $query = Admission::where('status', 0);
 
            if (!empty($keyword)) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('father_name', 'like', "%{$keyword}%")
                    ->orWhere('original_id', 'like', "%{$keyword}%");
                });
            }

            $total = $query->count();
            $data = $query
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get();

            return success_response([
                'data' => $data,
                'pagination' => [
                    'total' => $total,
                    'per_page' => (int)$perPage,
                    'current_page' => (int)$page,
                    'last_page' => ceil($total / $perPage),
                ]
            ]);
        } catch (Exception $e) {
            return error_response($e->getMessage());
        }
    }

    
    public function store(ExistingStudentRegisterRequest $request){
        DB::beginTransaction();
        try {
            $dob = Carbon::parse($request->input('dob')); 
            $currentDate = Carbon::now();  
            $ageMonths = $dob->diffInMonths($currentDate);

            $user = User::create([
                'name' => $request->input('name'),
                'phone' => $request->input('contact_number_1'),
                'email' => $request->input('email'),
                'password' => bcrypt($request->input('password', '123456')), 
                'dob' => $request->input('dob'),
                'age' => $ageMonths,
                'dob_hijri' => $request->input('dob_hijri'),
                'user_type' => 'student', 
            ]);
 
            Admission::create([
                'user_id' => $user->id, 
                'name' => $request->input('name'),
                'father_name' => $request->input('father_name'),
                'department_id' => $request->input('department_id'),  
                'interested_session' => $request->interested_session,
                'last_year_session' => $request->last_year_session,
                'last_year_id' => $request->last_year_id,
                'original_id' => $request->original_id,
                'total_marks' => $request->total_marks,
                'average_marks' => $request->average_marks,
            ]);
 
            
            Guardian::create([
                'user_id'               => $user->id,
                'guardian_name'         => $request->input('guardian_name'),
                'guardian_relation'     => $request->input('guardian_relation'),
                'guardian_occupation_details'   => $request->input('guardian_occupation_details'), 
                'guardian_education'    => $request->input('guardian_education'),                
                'children_count'        => $request->input('children_count'),
                'child_education'       => $request->input('child_education'),
                'contact_number_1'      => $request->input('contact_number_1'),
                'contact_number_2'      => $request->input('contact_number_2'),
                'whatsapp_number'       => $request->input('whatsapp_number'),
                'same_address'          => $request->input('same_address'),
            ]);

            UserAddress::create([
                'user_id'  => $user->id,
                'address_type'      => 'permanent',
                'house_or_state'    => $request->input('house_or_state'),
                'village_or_area'  => $request->input('village_or_area'),
                'post_office'       => $request->input('post_office'),
                'upazila_thana'     => $request->input('upazila_thana'), 
                'district'          => $request->input('district'),
                'division'          => $request->input('division'),
            ]);

            if (!$request->same_address) {
                UserAddress::create([
                    'user_id'  => $user->id,
                    'address_type'      => 'temporary',
                    'house_or_state'    => $request->input('temporary_house_or_state'),
                    'village_or_area'  => $request->input('temporary_village_or_area'),
                    'post_office'       => $request->input('temporary_post_office'),
                    'upazila_thana'     => $request->input('temporary_upazila_thana'), 
                    'district'          => $request->input('temporary_district'),
                    'division'          => $request->input('temporary_division'),
                ]);
            }  
            DB::commit();
            return success_response($request->all(), 'অভিনন্দন! আপনার নিবন্ধন সফলভাবে সম্পন্ন হয়েছে।',  201);
        } catch (\Exception $e) {
            DB::rollback();
            return error_response($e->getMessage(), 500);
        }
    }    
    public function approve(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'fee_type' => 'required',
            'reg_id' => ['required', 'regex:/^[0-9]+$/', 'unique:users,reg_id'],
            'jamaat' => ['nullable', 'regex:/^[0-9]+$/'],
        ], [
            'fee_type.required' => 'ফি টাইপ দেওয়া আবশ্যক।',
            'reg_id.required' => 'রেজিস্ট্রেশন আইডি দেওয়া আবশ্যক।',
            'reg_id.regex' => 'রেজিস্ট্রেশন আইডি অবশ্যই শুধুমাত্র ইংরেজি সংখ্যা (0-9) হতে হবে।',
            'reg_id.unique' => 'এই রেজিস্ট্রেশন আইডি ইতিমধ্যে ব্যবহার করা হয়েছে।',
            'jamaat.regex' => 'জামাত অবশ্যই শুধুমাত্র ইংরেজি সংখ্যা (0-9) হতে হবে।',
        ]);
        
        
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'ভ্যালিডেশন ত্রুটি।',
                'errors' => $validator->errors()
            ], 422);
        }
               

        if ($validator->fails()) {
            return error_response(null, 422, $validator->errors());
        }
        
        DB::beginTransaction(); 
        try {
            $admission = Admission::find($id);

            if (!$admission) {
                return error_response(null, '404', "ভুল আইডি প্রদান করা হয়েছে, ভর্তি তথ্য পাওয়া যায়নি।");
            }

            if ($admission->status == 1) {
                return error_response(null, '409', "এই শিক্ষার্থী ইতোমধ্যে ভর্তি হয়েছে।");
            }

            $user = User::find($admission->user_id);
            $user->reg_id = $request->reg_id;
            $user->save();

            $student = Student::create([
                'user_id' => $admission->user_id, 
                'reg_id' => $admission->reg_id,
                'jamaat' => $request->jamaat??null,
                "average_marks" => $admission->average_marks,
                "status" => 1
            ]);

            Enrole::create([
                'user_id' => $admission->user_id,
                'student_id' => $student->id,
                'department_id' => $admission->department_id,
                'session' => $admission->last_year_session,
                'year' => 1445,
                "marks" => $admission->total_marks,
                "fee_type" => $request->fee_type,
                "fee" => 0,
                "status" => 2,
            ]);

            $enrole = Enrole::create([
                'user_id' => $admission->user_id,
                'student_id' => $student->id,
                'department_id' => $admission->department_id,
                'session' => $admission->interested_session,
                'year' => 1446,
                "fee_type" => $request->fee_type,
                "fee" => $request->fee ?? null,
                "status" => 1,
            ]);

            $admission->status = 1;
            $admission->save();

            $active_month = HijriMonth::where('is_active', true)->first();
            if (!$active_month) {
                DB::rollBack();
                return error_response(null, '422', "কোনো সক্রিয় হিজরি মাস পাওয়া যায়নি। অনুগ্রহ করে আগে সক্রিয় মাস নির্ধারণ করুন।");
            }

            if ($admission->department_id == 1) {
                $monthly_fee = FeeSetting::where('key', 'maktab_monthly_fee')->value('value') ?? 0;
                $admission_fee = FeeSetting::where('key', 'maktab_admission_fee')->value('value') ?? 0;
            } else {
                $monthly_fee = FeeSetting::where('key', 'kitab_monthly_fee')->value('value') ?? 0;
                $admission_fee = FeeSetting::where('key', 'kitab_admission_fee')->value('value') ?? 0;
            }

            Payment::create([
                'user_id' => $admission->user_id,
                'student_id' => $student->id,
                'hijri_month_id' => $active_month->id,
                'reason' => "ভর্তি ফি",
                'year' => $enrole->year,
                'amount' => $admission_fee,
                'due' => $admission_fee,
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id,
            ]);

            if ($request->fee_type == "আংশিক") {
                $monthly_fee = $request->fee;
            }

            Payment::create([
                'user_id' => $admission->user_id,
                'student_id' => $student->id,
                'hijri_month_id' => $active_month->id,
                'reason' => "মাসিক ফি",
                'year' => $enrole->year,
                'fee_type' => $request->fee_type,
                'amount' => $monthly_fee,
                'due' => $monthly_fee,
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id,
            ]);

            DB::commit();
            return success_response(null, "✅ ভর্তি সফলভাবে সম্পন্ন হয়েছে।");

        } catch (\Exception $e) {
            DB::rollBack();
            return error_response(null, '500', "❌ ভর্তি প্রক্রিয়ায় একটি ত্রুটি ঘটেছে: " . $e->getMessage());
        }
    } 

}

