<?php

namespace App\Enums;

final class Department
{
    const Maktab  = 1;
    const Kitab    = 2; 

    public static function values()
    {
        return [
            self::Maktab  => 'মক্তব',
            self::Kitab     => 'কিতাব',
        ];
    }
}
