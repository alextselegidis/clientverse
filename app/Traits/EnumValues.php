<?php

namespace App\Traits;

trait EnumValues
{
    public static function values(): array
    {
        return array_map(fn(self $case) => $case->value, self::cases());
    }
}
