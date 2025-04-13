<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\TeacherComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TeacherCommentController extends Controller
{
    public function index(Request $request)
    {
        $student_id = $request->student_id;

        $perPage = $request->per_page ?? 10;
        $page = $request->page ?? 1;
        $offset = ($page - 1) * $perPage;

        $query = TeacherComment::with('teacher') // eager load teacher
            ->where('student_id', $student_id)
            ->latest();

        $total = $query->count();

        $comments = $query->skip($offset)
                        ->take($perPage)
                        ->get();

        // Transform the data
        $data = $comments->map(function ($comment) {
            return [
                'teacher_id' => $comment->teacher_id,
                'teacher_name' => optional($comment->teacher)->name,
                'comment' => $comment->comment,
                'created_at' => $comment->created_at->toDateTimeString(),
            ];
        });

        return success_response([
            'data' => $data,
            'pagination' => [
                'total' => $total,
                'per_page' => (int) $perPage,
                'current_page' => (int) $page,
                'last_page' => ceil($total / $perPage),
            ],
        ]);
    }



    public function store(Request $request){ 
        $validator = Validator::make($request->all(),[
            'student_id' => 'required|exists:users,id',  
            'comment' => 'required|string|max:1000', 
        ]);
        if($validator->fails()){
            return error_response(null, 422, $validator->errors());
        } 
     
        TeacherComment::create([
            'teacher_id' => Auth::user()->id,
            'student_id' => $request->student_id,
            'comment' => $request->comment,
        ]);
        return success_response(null,'Comment added successfully');
    }
}
