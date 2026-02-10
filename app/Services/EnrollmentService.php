<?php

namespace App\Services;

use App\Enums\FeeReason;
use App\Enums\FeeType;
use App\Models\Enrole;
use App\Models\HijriMonth;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;

class EnrollmentService
{
    /**
     * Calculate monthly fee based on fee type and settings
     * 
     * @param int $fee_type
     * @param float|null $custom_fee (User input fee)
     * @param float|null $standard_fee (Fee from settings)
     * @return array ['fee_type' => int, 'monthly_fee' => float, 'regular_monthly_fee' => float]
     */
    public static function calculateFeeType($fee_type, $custom_fee = null, $standard_fee = 0)
    {
        $monthly_fee = 0;
        $regular_monthly_fee = 0;
        $final_fee_type = $fee_type;
        $custom_fee = $custom_fee ?? 0;
        $standard_fee = $standard_fee ?? 0;

        if ($fee_type == FeeType::General) {
            $monthly_fee = $standard_fee;
            $regular_monthly_fee = $standard_fee;
        } elseif ($fee_type == FeeType::Half) {
            $monthly_fee = $custom_fee;
            $regular_monthly_fee = $custom_fee;
        } elseif ($fee_type == FeeType::Guest) {
            $monthly_fee = 0;
            $regular_monthly_fee = 0;
        } elseif ($fee_type == FeeType::HalfButThisMonthGeneral) {
            $monthly_fee = $standard_fee;
            $regular_monthly_fee = $custom_fee;
        } elseif ($fee_type == FeeType::GuestButThisMonthGeneral) {
            $monthly_fee = $standard_fee;
            $regular_monthly_fee = 0;
        }

        return [
            'fee_type' => $fee_type,
            'monthly_fee' => $monthly_fee,
            'regular_monthly_fee' => $regular_monthly_fee,
        ];
    }

    /**
     * Create enrollment with payments
     * 
     * @param array $enrollmentData [
     *   'user_id' => int,
     *   'student_id' => int,
     *   'department_id' => int,
     *   'session' => int,
     *   'year' => string|null, // If null, will use active month year
     *   'roll_number' => int|null,
     *   'marks' => string|null,
     *   'fee_type' => int,
     *   'fee' => float|null, (Custom Fee)
     *   'standard_monthly_fee' => float|null, (Standard Fee from Settings)
     *   'status' => int (default: 1),
     *   'admission_fee' => float|null,
     *   'create_payments' => bool (default: true), // Whether to create payment records
     * ]
     * @return Enrole
     * @throws \Exception
     */
    public static function createEnrollment(array $enrollmentData)
    {
        // Get active month
        $active_month = HijriMonth::where('is_active', true)->first();
        if (!$active_month) {
            throw new \Exception("কোন অ্যাকটিভ হিজরি মাস নেই।");
        }

        // Get year from data or use active month year
        $year = $enrollmentData['year'] ?? $active_month->year;

        // Calculate fee type
        $feeCalculation = self::calculateFeeType(
            $enrollmentData['fee_type'],
            $enrollmentData['fee'] ?? null,
            $enrollmentData['standard_monthly_fee'] ?? 0
        );

        // Create enrollment
        $enrole = Enrole::create([
            'user_id' => $enrollmentData['user_id'],
            'student_id' => $enrollmentData['student_id'],
            'department_id' => $enrollmentData['department_id'],
            'session' => $enrollmentData['session'],
            'year' => $year,
            'roll_number' => $enrollmentData['roll_number'] ?? null,
            'marks' => $enrollmentData['marks'] ?? null,
            'fee_type' => $feeCalculation['fee_type'],
            'fee' => $enrollmentData['fee'] ?? null, // Storing what was passed (custom fee usually)
            'status' => $enrollmentData['status'] ?? 1,
        ]);

        // Create payments if requested
        $createPayments = $enrollmentData['create_payments'] ?? true;
        if ($createPayments) {
            self::createPayments([
                'enrole' => $enrole,
                'user_id' => $enrollmentData['user_id'],
                'student_id' => $enrollmentData['student_id'],
                'active_month' => $active_month,
                'admission_fee' => $enrollmentData['admission_fee'] ?? 0,
                'fee_type' => $feeCalculation['fee_type'],
                'monthly_fee' => $feeCalculation['monthly_fee'],
                'regular_monthly_fee' => $feeCalculation['regular_monthly_fee'],
            ]);
        }

        return $enrole;
    }

    /**
     * Create payment records for enrollment
     * 
     * @param array $paymentData
     * @return void
     */
    public static function createPayments(array $paymentData)
    {
        $enrole = $paymentData['enrole'];
        $user_id = $paymentData['user_id'];
        $student_id = $paymentData['student_id'];
        $active_month = $paymentData['active_month'];
        $admission_fee = $paymentData['admission_fee'] ?? 0;
        $fee_type = $paymentData['fee_type'];
        $monthly_fee = $paymentData['monthly_fee'];
        $regular_monthly_fee = $paymentData['regular_monthly_fee'];
        $created_by = Auth::id();
        $updated_by = Auth::id();

        // Create Admission Fee Payment if provided
        if ($admission_fee >= 0) {
            Payment::create([
                'user_id' => $user_id,
                'student_id' => $student_id,
                'enrole_id' => $enrole->id,
                'hijri_month_id' => $active_month->id,
                'reason' => FeeReason::ADMISSION,
                'year' => $enrole->year,
                'amount' => $admission_fee,
                'due' => $admission_fee,
                'created_by' => $created_by,
                'updated_by' => $updated_by,
            ]);
        }

        // Create Monthly Fee Payment for current month
        Payment::create([
            'user_id' => $user_id,
            'student_id' => $student_id,
            'enrole_id' => $enrole->id,
            'hijri_month_id' => $active_month->id,
            'reason' => FeeReason::MONTHLY,
            'year' => $enrole->year,
            'fee_type' => $fee_type,
            'amount' => $monthly_fee,
            'due' => $monthly_fee,
            'created_by' => $created_by,
            'updated_by' => $updated_by,
        ]);

        // Create Monthly Fee Payments for future months
        $future_months = HijriMonth::where('id', '>', $active_month->id)->get();
        foreach ($future_months as $month) {
            Payment::create([
                'user_id' => $user_id,
                'student_id' => $student_id,
                'enrole_id' => $enrole->id,
                'hijri_month_id' => $month->id,
                'reason' => FeeReason::MONTHLY,
                'year' => $enrole->year,
                'fee_type' => $fee_type,
                'amount' => $regular_monthly_fee,
                'due' => $regular_monthly_fee,
                'created_by' => $created_by,
                'updated_by' => $updated_by,
            ]);
        }
    }
}

