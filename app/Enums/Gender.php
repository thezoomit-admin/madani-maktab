<?php

namespace App\Enums;

final class Gender
{
    const Male = "1";
    const Female = "2";
    const Others = "3";

    public static function values()
    {
        return [
            self::Male => 'Male',
            self::Female => 'Female',
            self::Others => 'Others',
        ];
    }
}
