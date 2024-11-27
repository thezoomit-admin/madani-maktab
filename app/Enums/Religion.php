<?php

namespace App\Enums;

final class Religion
{
    const Islam         = "1";
    const Christianity  = "2";
    const Hinduism      = "3";
    const Buddhism      = "4";
    const Judaism       = "5";
    const Sikhism       = "6";
    const Jainism       = "7";
    const Baha          = "8";
    const Confucianism  = "9";
    const Others        = "10";

    public static function values()
    {
        return [
            self::Islam         => 'Islam',
            self::Christianity  => 'Christianity',
            self::Hinduism      => 'Hinduism',
            self::Buddhism      => 'Buddhism',
            self::Judaism       => 'Judaism',
            self::Sikhism       => 'Sikhism',
            self::Jainism       => 'Jainism',
            self::Baha          => 'Bahá\'í Faith',
            self::Confucianism  => 'Confucianism',
            self::Others        => 'Others',
        ];
    }
}
