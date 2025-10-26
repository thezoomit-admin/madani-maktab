<?php

namespace App\Traits;

trait HandlesStudentStatus
{
    private function statusRules()
    {
        return [
            'normal_failed_message_send' => [
                'is_passed_age' => false,
                'is_send_fail_message' => true,
            ],
            'normal_failed_message_not_send' => [
                'is_passed_age' => false,
                'is_send_fail_message' => null,
            ],
            'message_not_sent' => [
                'is_passed_age' => true,
                'is_send_step_2_link' => null,
            ],
            'message_sent' => [
                'is_send_step_2_link' => true,
                'is_registration_complete' => null,
            ],
            'second_step_completed' => [
                'is_registration_complete' => true,
                'is_interview_scheduled' => null,
            ],
            'exam_message_sent' => [
                'is_interview_scheduled' => true,
                'is_first_exam_completed' => null,
            ],
            'first_exam_completed' => [
                'is_first_exam_completed' => true,
                'is_passed_interview' => null,
            ],
            'passed' => [
                'is_passed_interview' => true,
                'is_invited_for_trial' => null,
            ],
            'failed' => [ 
                'is_passed_interview' => false,
                'is_invited_for_trial' => null,
            ],
            'invited' => [
                'is_invited_for_trial' => true,
                'is_present_in_madrasa' => null,
            ],
            'present_in_madrasa' => [
                'is_present_in_madrasa' => true,
                'is_passed_trial' => null,
            ],
            'observation_passed' => [
                'is_passed_trial' => true, 
            ],
            'observation_failed' => [
                'is_passed_trial' => false, 
            ],
            'admission_completed' => [
                'is_admission_completed' => true,
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
            if ($value === null) {
              if ($progress->$field !== $value) return false;
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
