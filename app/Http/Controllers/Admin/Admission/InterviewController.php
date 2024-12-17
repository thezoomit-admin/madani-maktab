<?php

namespace App\Http\Controllers\Admin\Admission;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Admission\InterviewScheduleRequest;
use App\Models\InterviewSchedule;
use Exception;
use Illuminate\Http\Request;

class InterviewController extends Controller
{ 
    public function store(InterviewScheduleRequest $request){ 
        $interview_date = $request->date . ' ' . $request->time; 
        try {   
            $schedule = InterviewSchedule::create([
                'candidate_id' => $request->candidate_id,
                'interviewer_id' => $request->interviewer_id,
                'interview_date' => $interview_date,
                'location' => $request->location ?? 'online', 
                'notes' => $request->notes, 
            ]); 
            return response()->json([
                'data' => $schedule,
                'message' => 'Interview schedule created successfully',
                'status' => true,
            ], 201);
        } catch (Exception $e) { 
            return response()->json([
                'data' => null,
                'message' => 'An error occurred while creating schedule',
                'status' => false,
            ], 500);
        }
    }
}
