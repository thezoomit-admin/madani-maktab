<?php

namespace App\Enums;

final class BloodGroup
{
    const APositive     = "1";
    const ANegative     = "2";
    const BPositive     = "3";
    const BNegative     = "4";
    const ABPositive    = "5";
    const ABNegative    = "6";
    const OPositive     = "7";
    const ONegative     = "8";

    public static function values()
    {
        return [
            self::APositive     => 'A+',
            self::ANegative     => 'A-',
            self::BPositive     => 'B+',
            self::BNegative     => 'B-',
            self::ABPositive    => 'AB+',
            self::ABNegative    => 'AB-',
            self::OPositive     => 'O+',
            self::ONegative     => 'O-',
        ];
    }
}
