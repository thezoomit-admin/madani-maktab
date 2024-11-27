<?php

namespace App\Enums;

final class Priority
{
    const High      = "1";
    const Regular   = "2";
    const Low       = "3";

    public static function values()
    {
        return [
            self::High      => 'High',
            self::Regular   => 'Regular',
            self::Low       => 'Low',
        ];
    }
}
