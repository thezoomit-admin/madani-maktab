<?php

namespace App\Http\Controllers\Admin\Admission;

use App\Http\Controllers\Controller;
use App\Models\StudentNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdmissionNoteController extends Controller
{
    public function show($id)
    { 
        $notes = StudentNote::where('student_id', $id)
            ->with('employee')  
            ->select('id', 'employee_id', 'student_id', 'notes', 'created_at') 
            ->get();
 
        if ($notes->isEmpty()) {
            return success_response([]); 
        }
 
        $datas = $notes->map(function ($note) {
            return [
                'id' => $note->id,
                'employee_name' => $note->employee->name,  
                'notes' => $note->notes,
                'created_at' => $note->created_at->toDateTimeString(), 
            ];
        }); 
        return success_response($datas);
    }


    public function store(Request $request){
        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',  
            'notes' => 'required|string|max:1000',   
        ]);
     
        StudentNote::create([
            'employee_id' => Auth::user()->id,
            'student_id' => $validated['student_id'],
            'notes' => $validated['notes'],
        ]);
        return success_response(null,'Note added successfully');
    }
}
