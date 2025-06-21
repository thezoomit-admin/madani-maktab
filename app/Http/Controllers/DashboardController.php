<?php

namespace App\Http\Controllers;

use App\Enums\Department;
use App\Models\Admission;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke()
    { 
        $maktab = $this->getStudentsByDepartment(1); 
        $kitab = $this->getStudentsByDepartment(2); 
        return success_response([
            "kitab" => $this->getStudentCounts($kitab),
            "maktab" => $this->getStudentCounts($maktab),
            "absent" => $this->getAbsentsByDepartment(),
        ]);
    }

    private function getStudentsByDepartment($departmentId)
    {
        return User::where('user_type', 'student')
            ->whereHas('studentRegister', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            })
            ->with('admissionProgress')  
            ->get();
    }

 
    private function getStudentCounts($students)
    {
        return [
            'total_application' => $students->count(),
            'general_fail' => $students->filter(function($student) {
                return $student->admissionProgress && $student->admissionProgress->is_passed_age === 0;
            })->count(),
            'general_pass' => $students->filter(function($student) {
                return $student->admissionProgress && $student->admissionProgress->is_passed_age === 1;
            })->count(),
            'interview_fail' => $students->filter(function($student) {
                return $student->admissionProgress && $student->admissionProgress->is_passed_age === 1 && $student->admissionProgress->is_passed_interview === 0;
            })->count(),
            'interview_pass' => $students->filter(function($student) {
                return $student->admissionProgress && $student->admissionProgress->is_passed_age === 1 && $student->admissionProgress->is_passed_interview === 1;
            })->count(),
            'final_fail' => $students->filter(function($student) {
                return $student->admissionProgress && $student->admissionProgress->is_passed_age === 1 && $student->admissionProgress->is_passed_interview === 1 && $student->admissionProgress->is_passed_trial === 0;
            })->count(),
            'final_pass' => $students->filter(function($student) {
                return $student->admissionProgress && $student->admissionProgress->is_passed_age === 1 && $student->admissionProgress->is_passed_interview === 1 && $student->admissionProgress->is_passed_trial === 1;
            })->count(),
        ];
    } 

    public function getAbsentsByDepartment()
    {
        $result = [];

        foreach (Department::values() as $deptId => $deptName) { 
            $students = Admission::where('department_id', $deptId)
                ->where('status', 1)
                ->get();

            // Filter absent students
            $absentStudents = $students->filter(function ($student) {
                $last = Attendance::where('user_id', $student->user_id)
                    ->latest('in_time')
                    ->first();

                // if no attendance or out_time is not null => Absent
                return !$last || $last->out_time !== null;
            });

            $result[] = [ 
                'department' => $deptName,
                'total_students'  => $students->count(),
                'absent_count'    => $absentStudents->count(),
                'absent_students' => $absentStudents->map(function ($s) {
                    return [
                        'user_id'  => $s->user_id,
                        'reg_id'   => $s->reg_id,
                        'name'     => $s->name,
                        'phone'     => $s->phone,
                    ];
                })->values(),
            ];
        }

        return $result; 
    }


}
