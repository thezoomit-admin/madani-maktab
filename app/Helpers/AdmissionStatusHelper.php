<?php

namespace App\Helpers;

class AdmissionStatusHelper
{
    // Status mapping
    public static function getStatusConditions()
    {
        return [
            'normal_failed_message_send' => [
                ['is_passed_age', false],
                ['is_send_fail_message', true],
            ],
            'normal_failed_message_not_send' => [
                ['is_passed_age', false],
                ['is_send_fail_message', null],
            ],
            'message_not_sent' => [
                ['is_passed_age', true],
                ['is_send_step_2_link', null],
            ],
            'message_sent' => [
                ['is_send_step_2_link', true],
                ['is_registration_complete', null],
            ],
            'second_step_completed' => [
                ['is_registration_complete', true],
                ['is_interview_scheduled', null],
            ],
            'exam_message_sent' => [
                ['is_interview_scheduled', true],
                ['is_first_exam_completed', null],
            ],
            'first_exam_completed' => [
                ['is_first_exam_completed', true],
                ['is_passed_interview', null],
            ],
            'passed' => [
                ['is_passed_interview', true],
                ['is_invited_for_trial', null],
            ],
            'failed' => [
                ['is_passed_interview', false],
            ],
            'invited' => [
                ['is_invited_for_trial', true],
                ['is_present_in_madrasa', null],
            ],
            'present_in_madrasa' => [
                ['is_present_in_madrasa', true],
                ['is_passed_trial', null],
            ],
            'observation_passed' => [
                ['is_passed_trial', true],
                ['is_admission_completed', null],
            ],
            'observation_failed' => [
                ['is_passed_trial', false],
            ],
            'admission_completed' => [
                ['is_admission_completed', true],
            ],
        ];
    }

    // একটি progress model থেকে status determine করা
    public static function determineStatus($progress)
    {
        if (!$progress) return 'N/A';

        foreach (self::getStatusConditions() as $status => $conditions) {
            $match = true;
            foreach ($conditions as [$field, $value]) {
                if ($progress->$field !== $value) {
                    $match = false;
                    break;
                }
            }
            if ($match) return $status;
        }

        return 'N/A';
    }
}
