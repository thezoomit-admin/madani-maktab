<?php

namespace App\Enums;

final class MaktabSession
{
    const PROTHOM_SRENI  = 1;
    const DITYO_SRENI    = 2;
    const TRITIO_SRENI   = 3;
    const CHOTURTHO_SRENI = 4;
    const PONCHOM_SRENI  = 5;

    public static function values()
    {
        return [
            self::PROTHOM_SRENI   => 'প্রথম শ্রেণি',
            self::DITYO_SRENI     => 'দ্বিতীয় শ্রেণি',
            self::TRITIO_SRENI    => 'তৃতীয় শ্রেণি',
            self::CHOTURTHO_SRENI => 'চতুর্থ শ্রেণি',
            self::PONCHOM_SRENI   => 'পঞ্চম শ্রেণি',
        ];
    }
}
