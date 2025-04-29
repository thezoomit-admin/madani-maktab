<?php

namespace App\Enums;

final class FeeType
{
    const General  = 1;
    const Half    = 2;
    const Guest   = 3; 

    public static function values()
    {
        return [
            self::General  => 'সাধারণ',
            self::Half     => 'আংশিক',
            self::Guest    => 'মেহমান', 
        ];
    }
}
