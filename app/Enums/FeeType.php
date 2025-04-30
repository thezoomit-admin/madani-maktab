<?php

namespace App\Enums;

final class FeeType
{
    const General              = 1;
    const Half                = 2;
    const Guest               = 3;
    const HalfButThisMonthGeneral = 4; 
    const GuestButThisMonthGeneral = 5;  

    public static function values()
    {
        return [
            self::General               => 'সাধারণ',
            self::Half                 => 'আংশিক',
            self::Guest                => 'মেহমান',
            self::HalfButThisMonthGeneral => 'আংশিক তবে এই মাস সাধারণ',
            self::GuestButThisMonthGeneral => 'মেহমান তবে এই মাস সাধারণ',
        ];
    }
}