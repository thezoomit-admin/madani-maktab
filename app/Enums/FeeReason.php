<?php

namespace App\Enums;

final class FeeReason
{
    const ADMISSION              = 1;
    const MONTHLY                = 2; 

    public static function values()
    {
        return [
            self::ADMISSION               => 'ভর্তি ফি',
            self::MONTHLY                 => 'মাসিক ফি',
        ];
    }
}