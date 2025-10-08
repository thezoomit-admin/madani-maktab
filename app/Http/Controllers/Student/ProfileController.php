<?php

namespace App\Http\Controllers\Student;

use App\Enums\ArabicMonth;
use App\Enums\Department;
use App\Enums\FeeReason;
use App\Enums\FeeType;
use App\Enums\KitabSession;
use App\Enums\MaktabSession;
use App\Http\Controllers\Controller;
use App\Models\Enrole;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function profile($id = null)
    {
        try {
            if (!$id) {
                $id = Auth::user()->id;
            }

            $user = User::with([
                    'studentRegister',
                    'address',
                    'guardian',
                    'userFamily',
                    'admissionProgress',
                    'answerFiles',
                    'student'
                ])
                ->where('id', $id)
                ->orWhereHas('studentRegister', function ($query) use ($id) {
                    $query->where('reg_id', $id);
                })
                ->first();

            if (!$user) {
                return error_response(null, 404, "ইউজার পাওয়া যায়নি।");
            }

            if (!$user->student) {
                return error_response(null, 404, "এই ইউজারের শিক্ষার্থী তথ্য পাওয়া যায়নি।");
            }

            $registration = $user->studentRegister;
            
            $basic = [
                'name' => $user->name,
                // 'phone' => $user->phone, 
                'profile_image' => $user->profile_image,
                'reg_id' => $user->reg_id,
                'dob_hijri' => $user->dob_hijri,
                'dob_english' => $user->dob,
                'age' => $user->age,
                'blood_group' => $user->blood_group,
                'jamaat' => optional($user->student)->jamaat,
                'average_marks' => optional($user->student)->average_marks,
                "previous_education_details" => optional($registration)->previous_education_details,
                'status' => optional($user->student)->status,
            ];

            // $education = [
            //     "department_id" => optional($registration)->department_id,
            //     "bangla_study_status" => optional($registration)->bangla_study_status,
            //     "bangla_others_study" => optional($registration)->bangla_others_study,
            //     "arabi_study_status" => optional($registration)->arabi_study_status,
            //     "arabi_others_study" => optional($registration)->arabi_others_study,
            //     "previous_education_details" => optional($registration)->previous_education_details,
            //     "hifz_para" => optional($registration)->hifz_para,
            //     "is_other_kitab_study" => optional($registration)->is_other_kitab_study,
            //     "kitab_jamat" => optional($registration)->kitab_jamat,
            //     "is_bangla_handwriting_clear" => optional($registration)->is_bangla_handwriting_clear,
            //     "note" => optional($registration)->note,
            //     "handwriting_image" => optional($registration)->handwriting_image,
            // ];

            $guardian_data = $user->guardian;
            $guardian = [
                'father_name' => optional($registration)->father_name,
                'guardian_name' => optional($guardian_data)->guardian_name,
                'guardian_relation' => optional($guardian_data)->guardian_relation,
                'guardian_occupation_details' => optional($guardian_data)->guardian_occupation_details,
                'guardian_education' => optional($guardian_data)->guardian_education,
                'children_count' => optional($guardian_data)->children_count,
                'child_education' => optional($guardian_data)->child_education,
                'contact_number_1' => optional($guardian_data)->contact_number_1,
                'contact_number_2' => optional($guardian_data)->contact_number_2,
                'whatsapp_number' => optional($guardian_data)->whatsapp_number,
                'email' => $user->email,
            ];

            // $family_data = $user->userFamily;
            // $family = [
            //     'deeni_steps' => optional($family_data)->deeni_steps,
            //     'follow_porada' => optional($family_data)->follow_porada,
            //     'shariah_compliant' => optional($family_data)->shariah_compliant,
            //     'motivation' => optional($family_data)->motivation,
            //     'info_src' => optional($family_data)->info_src,
            //     'first_contact' => optional($family_data)->first_contact,
            //     'preparation' => optional($family_data)->preparation,
            //     'clean_lang' => optional($family_data)->clean_lang,
            //     'future_plan' => optional($family_data)->future_plan,
            //     'years_at_inst' => optional($family_data)->years_at_inst,
            //     'reason_diff_edu' => optional($family_data)->reason_diff_edu,
            //     'separation_experience' => optional($family_data)->separation_experience,
            //     'is_organize_items' => optional($family_data)->is_organize_items,
            //     'is_wash_clothes' => optional($family_data)->is_wash_clothes,
            //     'is_join_meal' => optional($family_data)->is_join_meal,
            //     'is_clean_after_bath' => optional($family_data)->is_clean_after_bath,
            //     'health_issue_details' => optional($family_data)->health_issue_details,
            //     'is_bath_before_sleep' => optional($family_data)->is_bath_before_sleep,
            // ];

            $datas = [
                "basic" => $basic,
                // "education" => $education,
                "address" => $user->address ?? null,
                "guardian"=> $guardian,
                // "family" => $family,
                "answerFiles" => $user->answerFiles ?? [],
            ];

            return success_response($datas);
        } catch (\Exception $e) {
            return error_response($e->getMessage());
        }
    }


    public function PaymentHistory(Request $request, $id = null)
    {
        try {
            if (!$id) {
                $id = Auth::id();
            }

            $user = User::find($id);
            if (!$user) {
                return error_response(null, 404, "ইউজার পাওয়া যায়নি।");
            }

            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);  
            $year = $request->input('year', null);  
            $query = Payment::with('hijriMonth')->where('user_id', $id);
 
            if ($year) {
                $query->where('year', $year);
            }  

            $total = $query->count(); 
            $payments = $query->skip(($page - 1) * $perPage)
                            ->take($perPage)
                            ->get();

            $datas = [];
            $totalAmount = 0;
            $totalPaid = 0;
            $totalDue = 0;  

            foreach ($payments as $payment) {
                
                if($payment->due == 0){
                    $status = "Paid";
                } else if($payment->transaction) {
                    if($payment->transaction->is_approved == false){
                        $status = "Pending";
                    } else {
                        $status = "Unpaid";
                    }
                } else {
                    $status = "Unpaid";
                }
                
                $month_id = optional($payment->hijriMonth)->month;
                $datas[] = [
                    'id' => $payment->id,
                    'month' => enum_name(ArabicMonth::class, $month_id) . ' - ' . optional($payment->hijriMonth)->year,
                    'reason' => enum_name(FeeReason::class, $payment->reason),
                    'fee_type' => enum_name(FeeType::class, $payment->fee_type),
                    'amount' => $payment->amount,
                    'status' => $status,
                ];
 
                $totalAmount += $payment->amount;
                $totalPaid += $payment->paid;
                $totalDue += $payment->due;
            }

            return success_response([
                'data' => $datas,
                'pagination' => [
                    'total' => $total,
                    'per_page' => (int) $perPage,
                    'current_page' => (int) $page,
                    'last_page' => ceil($total / $perPage),
                ],
                'totals' => [
                    'total_amount' => $totalAmount,
                    'total_paid' => $totalPaid,
                    'total_due' => $totalDue,
                ],
            ]);  

        } catch (\Exception $e) {
            return error_response(null, 500, $e->getMessage());
        }
    }


    public function EnroleHistory(Request $request, $id = null)
    {
        try {
            if (!$id) {
                $id = Auth::id();
            }

            $user = User::find($id);
            if (!$user) {
                return error_response(null, 404, "ইউজার পাওয়া যায়নি।");
            }

            $perPage = (int) $request->input('per_page', 10);
            $page = (int) $request->input('page', 1);

            $query = Enrole::where("user_id", $id)->latest();

            $total = $query->count();

            $enroles = $query
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get();
 
            $data = $enroles->map(function ($enrole) {
                if($enrole->department_id==1){
                    $session =  enum_name(MaktabSession::class, $enrole->session);
                }else{
                    $session =  enum_name(KitabSession::class, $enrole->session);
                } 

                return [
                    'id' => $enrole->id,
                    'student_id' => $enrole->student_id,
                    'department_id' => enum_name(Department::class,  $enrole->department_id),
                    'session' => $session,
                    'year' => $enrole->year,
                    'marks' => $enrole->marks,
                    'fee_type' => enum_name(FeeType::class, $enrole->fee_type),
                    'fee' => $enrole->fee,
                    'status' => $enrole->status,
                ];
            });

            return success_response([
                'data' => $data,
                'pagination' => [
                    'total' => $total,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'last_page' => ceil($total / $perPage),
                ]
            ]);
        } catch (\Exception $e) {
            return error_response(null, 500, 'এনরোল হিস্টোরি লোড করতে সমস্যা হয়েছে।');
        }
    }   
    
}
