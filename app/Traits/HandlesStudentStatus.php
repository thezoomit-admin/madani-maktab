<?php

namespace App\Traits;

trait HandlesStudentStatus
{
    private function statusRules()
    {
        return [
            'not_interested' => [
                'is_interested' => false,
            ],
            'normal_failed_message_send' => [
                'is_passed_age' => false,
                'is_send_fail_message' => true,
                'is_interested' => true,
            ],
            'normal_failed_message_not_send' => [
                'is_passed_age' => false,
                'is_send_fail_message' => null,
                'is_interested' => true,
            ],
            'message_not_sent' => [
                'is_passed_age' => true,
                'is_send_step_2_link' => null,
                'is_interested' => true,
            ],
            'message_sent' => [
                'is_send_step_2_link' => true,
                'is_registration_complete' => null,
                'is_interested' => true,
            ],
            'second_step_completed' => [
                'is_registration_complete' => true,
                'is_interview_scheduled' => null,
                'is_passed_age' => true,
                'is_interested' => true,
            ],
            'exam_message_sent' => [
                'is_interview_scheduled' => true,
                'is_first_exam_completed' => null,
                'is_interested' => true,
            ],
            'first_exam_completed' => [
                'is_first_exam_completed' => true,
                'is_passed_interview' => null,
                'is_interested' => true,
            ],
            'passed' => [
                'is_passed_interview' => true,
                'is_invited_for_trial' => null,
                'is_interested' => true,
            ],
            'failed' => [ 
                'is_passed_interview' => false,
                'is_interested' => true,
            ],
            'invited' => [
                'is_invited_for_trial' => true,
                'is_present_in_madrasa' => null,
                'is_interested' => true,
            ],
            'present_in_madrasa' => [
                'is_present_in_madrasa' => true,
                'is_passed_trial' => null,
                'is_interested' => true,
            ],
            'observation_passed' => [
                'is_passed_trial' => true,
                'is_admission_completed' => null,
                'is_interested' => true,
            ],
            'observation_failed' => [
                'is_passed_trial' => false,
                'is_interested' => true,
            ],
            'admission_completed' => [ 
                'is_admission_completed' => true,
                'is_interested' => true,
            ],
        ];
    }

  
    public function applyStatusCondition($query, ?string $status)
    {
        $rules = $this->statusRules();

        if (isset($rules[$status])) {
            $conditions = $rules[$status];
            $query->whereHas('admissionProgress', function ($q) use ($conditions) {
                foreach ($conditions as $field => $value) {
                    if (is_null($value)) {
                        $q->whereNull($field);
                    } else {
                        $q->where($field, $value);
                    }
                }
            });
        }

        return $query;
    }

  
    public function determineStatus($progress)
    {
        if (!$progress) return 'no_data';

        foreach ($this->statusRules() as $status => $conditions) {
            if ($this->matchesAll($progress, $conditions)) {
                return $status;
            }
        }

        return 'unknown';
    }

 
    private function matchesAll($progress, array $conditions)
{
    foreach ($conditions as $field => $value) {
        $fieldValue = $progress->$field;

        if ($value === null) {
            if (!is_null($fieldValue)) return false;
        } elseif ($value === false) {
            if ($fieldValue !== false && $fieldValue !== 0) return false;
        } elseif ($value === true) {
            if ($fieldValue !== true && $fieldValue !== 1) return false;
        } else {
            if ($fieldValue !== $value) return false;
        }
    }
    return true;
}


 
    public function getStudentCounts($students)
    {
        $counts = [];
         $allCount = 0;
        foreach (array_keys($this->statusRules()) as $status) {
            $counts[$status] = $students->filter(fn($s) =>
                $s->admissionProgress && $this->matchesAll($s->admissionProgress, $this->statusRules()[$status])
            )->count();

             $allCount = $allCount + $counts[$status];
        }
        $counts['all'] = $allCount;
        return $counts;
    }
}
