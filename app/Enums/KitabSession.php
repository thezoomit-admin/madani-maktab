<?php

namespace App\Enums;

final class KitabSession
{
    const PROTHOM_BORSHO  = 1;
    const YEADA           = 2;
    const DITYO_BORSHO    = 3;
    const TRITIO_BORSHO   = 4;
    const CHOTURTHO_BORSHO = 5;
    const PONCHOM_BORSHO  = 6;
    const SOYOM_BORSHO    = 7;
    const SOPTOM_BORSHO   = 8;

    public static function values()
    {
        return [
            self::PROTHOM_BORSHO   => 'প্রথম বর্ষ',
            self::YEADA            => 'ইয়াদা',
            self::DITYO_BORSHO     => 'দ্বিতীয় বর্ষ',
            self::TRITIO_BORSHO    => 'তৃতীয় বর্ষ',
            self::CHOTURTHO_BORSHO => 'চতুর্থ বর্ষ',
            self::PONCHOM_BORSHO   => 'পঞ্চম বর্ষ',
            self::SOYOM_BORSHO     => 'ষষ্ঠ বর্ষ',
            self::SOPTOM_BORSHO    => 'সপ্তম বর্ষ',
        ];
    }
}
