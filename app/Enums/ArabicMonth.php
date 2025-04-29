<?php

namespace App\Enums;

final class ArabicMonth
{
    const RAMADAN        = "1";
    const SHAWWAL        = "2";
    const DHU_AL_QIDAH   = "3";
    const DHU_AL_HIJJAH  = "4";
    const MUHARRAM       = "5";
    const SAFAR          = "6";
    const RABI_AL_AWWAL  = "7";
    const RABI_AL_THANI  = "8";
    const JUMADA_AL_AWWAL = "9";
    const JUMADA_AL_THANI = "10";
    const RAJAB          = "11";
    const SHABAN         = "12";

    public static function values()
    {
        return [
            self::RAMADAN         => 'রমজান',
            self::SHAWWAL         => 'শাওয়াল',
            self::DHU_AL_QIDAH    => 'জিলকদ',
            self::DHU_AL_HIJJAH   => 'জিলহজ্জ',
            self::MUHARRAM        => 'মুহাররম',
            self::SAFAR           => 'সফর',
            self::RABI_AL_AWWAL   => 'রবিউল আউয়াল',
            self::RABI_AL_THANI   => 'রবিউস সানি',
            self::JUMADA_AL_AWWAL => 'জুমাদাল আউয়াল',
            self::JUMADA_AL_THANI => 'জুমাদাস সানি',
            self::RAJAB           => 'রজব',
            self::SHABAN          => 'শাবান',
        ];
    }
}
