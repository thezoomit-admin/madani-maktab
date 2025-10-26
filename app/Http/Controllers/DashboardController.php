<?php

namespace App\Http\Controllers;

use App\Enums\Department;
use App\Models\Admission;
use App\Models\Attendance;
use App\Models\HijriMonth;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\HandlesStudentStatus;

class DashboardController extends Controller
{
    use HandlesStudentStatus;
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
        $active_month = HijriMonth::where('is_active', true)->first(); 
        $query = User::where('user_type', 'student')
            ->whereHas('studentRegister', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            })
            ->with('admissionProgress');
        if ($active_month) {
            $range = HijriMonth::getYearRange($active_month->year);
            if ($range) {
                $query->whereBetween('created_at', [$range['start_date'], $range['end_date']]);
            }
        }
        return $query->get();
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
