<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);
            $year = $request->input('year', 1446);

            $students = Student::with([
                'user:id,name,phone,profile_image,blood_group',
                'enroles' => function ($query) use ($year) {
                    $query->where('year', $year)
                        ->select('id', 'student_id', 'department_id', 'session', 'fee_type', 'status', 'year');
                }
            ])
            ->when($request->input('jamaat'), function ($query, $jamaat) {
                $query->where('jamaat', $jamaat);
            }) 
            ->whereHas('user', function ($query) use ($request) {
                if ($request->filled('blood_group')) {
                    $query->where('blood_group', $request->input('blood_group'));
                }
            })
            ->select('id', 'user_id',  'jamaat', 'average_marks', 'status')
            ->orderBy('id', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

            // Modify the paginated data
            $modified = $students->getCollection()->transform(function ($student) {
                $user = $student->user;
                $enrole = $student->enroles->first();

                return [
                    'id' => $student->id,
                    'user_id' => $student->user_id,
                    'reg_id' => $user->reg_id,
                    'jamaat' => $student->jamaat,
                    'average_marks' => $student->average_marks,

                    'name' => $user->name ?? null,
                    'phone' => $user->phone ?? null,
                    'profile_image' => $user->profile_image ?? null,
                    'blood_group' => $user->blood_group ?? null,

                    'department_id' => $enrole->department_id ?? null,
                    'session' => $enrole->session ?? null,
                    'fee_type' => $enrole->fee_type ?? null,
                    'enrole_status' => $enrole->status ?? null,
                    'year' => $enrole->year ?? null,
                ];
            });

            $students->setCollection($modified);

            return success_response([
                'data' => $students->items(),
                'pagination' => [
                    'total' => $students->total(),
                    'per_page' => $students->perPage(),
                    'current_page' => $students->currentPage(),
                    'last_page' => $students->lastPage(),
                ]
            ]);
        } catch (\Exception $e) {
            return error_response(null, 500, $e->getMessage());
        }
    }

 
    
}
