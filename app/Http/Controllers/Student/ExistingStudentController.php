<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExistingStudentRegisterRequest;
use App\Models\Admission;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use App\Models\UserAddress;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExistingStudentController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 20);  
            $page = $request->input('page', 1); 

            $query = Admission::where('status', 0);

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

    public function approve($id){
        $admisison = Admission::find($id); 
        if($admisison){
            return error_response(null,'404', "Invalid Id");
        }

        $student = Student::create([

        ]);
        
    }
}
