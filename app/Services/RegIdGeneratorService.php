<?php

namespace App\Services;

use App\Enums\Department;
use App\Enums\MaktabSession;
use App\Enums\KitabSession;
use App\Models\HijriMonth;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RegIdGeneratorService
{
    /**
     * Generate registration ID based on department, session, and active year
     *
     * @param int $department_id Department ID (1 = Maktab, 2 = Kitab)
     * @param int|string $session Session number (from MaktabSession or KitabSession enum)
     * @return string Generated registration ID
     */
    public function generate(int $department_id, $session): string
    {
        // Get active year (last 2 digits)
        $active_month = HijriMonth::where('is_active', true)->first();
        if (!$active_month) {
            throw new \Exception("কোন অ্যাকটিভ হিজরি মাস নেই।");
        }

        $year = substr($active_month->year, -2); // Get last 2 digits (e.g., 47 from 1447)
        $session_number = (int) $session; // Ensure session is integer

        if ($department_id == Department::Maktab) {
            return $this->generateMaktabRegId($year, $session_number);
        } else {
            return $this->generateKitabRegId($year, $session_number);
        }
    }

    /**
     * Generate Maktab registration ID
     * Format: {year}{session}{sequence}
     * Example: 47101, 47102, 47201, etc.
     *
     * @param string $year Last 2 digits of year (e.g., "47")
     * @param int $session Session number (1-5)
     * @return string
     */
    private function generateMaktabRegId(string $year, int $session): string
    {
        $prefix = $year . $session; // e.g., "471" for year 47, session 1
        $expectedLength = strlen($prefix) + 2; // Total length: prefix + 2 digits (e.g., 5 for "47101")

        // Find the last reg_id matching this pattern
        // Pattern: starts with prefix, total length matches
        $lastRegId = User::where('reg_id', 'like', $prefix . '%')
            ->whereRaw('LENGTH(reg_id) = ?', [$expectedLength])
            ->whereRaw('SUBSTRING(reg_id, 1, ?) = ?', [strlen($prefix), $prefix])
            ->orderByRaw('CAST(SUBSTRING(reg_id, -2) AS UNSIGNED) DESC')
            ->value('reg_id');

        if ($lastRegId) {
            // Extract sequence number (last 2 digits)
            $sequence = (int) substr($lastRegId, -2);
            $sequence++;
        } else {
            $sequence = 1; // Start from 01
        }

        // Format sequence as 2 digits (01, 02, 03, etc.)
        $formattedSequence = str_pad($sequence, 2, '0', STR_PAD_LEFT);

        return $prefix . $formattedSequence; // e.g., "47101"
    }

    /**
     * Generate Kitab registration ID
     * Format: 0{year}{session}{sequence}
     * Example: 047101, 047102, 047201, etc.
     *
     * @param string $year Last 2 digits of year (e.g., "47")
     * @param int $session Session number (0-7)
     * @return string
     */
    private function generateKitabRegId(string $year, int $session): string
    {
        $prefix = '0' . $year . $session; // e.g., "0471" for year 47, session 1
        $expectedLength = strlen($prefix) + 2; // Total length: prefix + 2 digits (e.g., 6 for "047101")

        // Find the last reg_id matching this pattern
        // Pattern: starts with prefix, total length matches
        $lastRegId = User::where('reg_id', 'like', $prefix . '%')
            ->whereRaw('LENGTH(reg_id) = ?', [$expectedLength])
            ->whereRaw('SUBSTRING(reg_id, 1, ?) = ?', [strlen($prefix), $prefix])
            ->orderByRaw('CAST(SUBSTRING(reg_id, -2) AS UNSIGNED) DESC')
            ->value('reg_id');

        if ($lastRegId) {
            // Extract sequence number (last 2 digits)
            $sequence = (int) substr($lastRegId, -2);
            $sequence++;
        } else {
            $sequence = 1; // Start from 01
        }

        // Format sequence as 2 digits (01, 02, 03, etc.)
        $formattedSequence = str_pad($sequence, 2, '0', STR_PAD_LEFT);

        return $prefix . $formattedSequence; // e.g., "047101"
    }
}

