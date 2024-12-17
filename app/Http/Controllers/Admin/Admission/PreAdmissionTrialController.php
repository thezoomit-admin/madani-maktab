<?php

namespace App\Http\Controllers\Admin\Admission;

use App\Http\Controllers\Controller;
use App\Models\AdmissionProgressStatus;
use App\Models\PreAdmissionTrial;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PreAdmissionTrialController extends Controller
{
    public function store(Request $request)
    { 
        $validator = Validator::make($request->all(), [
            'candidate_id' => 'required|exists:users,id',
            'date'         => ['required', 'date', 'after:now'],
            'time'         => ['required', 'date_format:H:i'],
            'note'         => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return error_response($validator->errors()->first(), 422);
        }

        DB::beginTransaction();
        try { 
            $requested_at = Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . $request->time);
 
            $progress = AdmissionProgressStatus::where('user_id',$request->candidate_id)->first();
            if(!$progress){
                return error_response('Candidate not found',404);
            } 

            $progress->is_invited_for_trial = true;
            $progress->save();  

            $trial = PreAdmissionTrial::updateOrCreate(
                ['candidate_id' => $request->candidate_id],
                [
                    'requested_at' => $requested_at,
                    'note'     => $request->note,
                ]
            ); 
            DB::commit();
            return success_response(null, "Trial request stored successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return error_response($e->getMessage(), 500);
        }
    } 

    public function attend(Request $request)
    { 
        $validator = Validator::make($request->all(), [
            'candidate_id' => 'required|exists:users,id',
            'date'         => ['required', 'date', 'after:now'],
            'time'         => ['required', 'date_format:H:i'],
            'note'         => 'nullable|string|max:500',
        ]);
 
        if ($validator->fails()) {
            return error_response($validator->errors()->first(), 422);
        }

        try { 
            $attended_at = Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . $request->time); 
            $trial = PreAdmissionTrial::where('candidate_id', $request->candidate_id)->first(); 
            if (!$trial) {
                return error_response('No trial record found for this candidate.', 404);
            } 
            $trial->update([
                'attended_at' => $attended_at,
                'status'      => 'attended',
                'note'        => $request->note,
            ]); 
            return success_response(null,"The student has successfully attended the trial session.");
        } catch (Exception $e) {
            return error_response($e->getMessage(), 500);
        }
    }

    public function result(Request $request)
    { 
        $validator = Validator::make($request->all(), [
            'candidate_id' => 'required|exists:pre_admission_trials,candidate_id',
            'note'        => 'nullable|string|max:1000',
            'result'       => 'required|boolean',
        ]);
 
        if ($validator->fails()) {
            return error_response($validator->errors()->first(), 422);
        }

        DB::beginTransaction();

        try { 
            $progress = AdmissionProgressStatus::where('user_id', $request->candidate_id)->first();
            if (!$progress) {
                return error_response('Candidate not found in admission progress status.', 404);
            }
 
            $progress->is_passed_trial = $request->result;
            $progress->save();
 
            $trial = PreAdmissionTrial::where('candidate_id', $request->candidate_id)->first();
            if (!$trial) {
                return error_response('No trial record found for this candidate.', 404);
            }
 
            $trial->status = 'completed';
            $trial->result = $request->result;
            $trial->note = $request->note; 
            $trial->save();

            DB::commit(); 

            return success_response(null, "The trial session result has been successfully updated.");

        } catch (Exception $e) {
            DB::rollBack(); // Rollback the transaction on error
            return error_response('An error occurred while processing the request: ' . $e->getMessage(), 500);
        }
    }



}
