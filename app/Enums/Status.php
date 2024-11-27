<?php

namespace App\Enums;

final class Status
{
    const Active = "1";
    const Inactive = "0";

    public static function values()
    {
        return [
            self::Active => 'Active',
            self::Inactive => 'Inactive',
        ];
    }
}
