<?php

namespace App\Enums;

final class MaritualStatus
{
    const Married   = "1";
    const Unmarried = "2";
    const Divorce   = "3";

    public static function values()
    {
        return [
            self::Married   => 'Married',
            self::Unmarried => 'Unmarried',
            self::Divorce   => 'Divorce',
        ];
    }
}
