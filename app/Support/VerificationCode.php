<?php

namespace App\Support;

final class VerificationCode
{
    public static function generate(): string
    {
        return str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
    }
}
