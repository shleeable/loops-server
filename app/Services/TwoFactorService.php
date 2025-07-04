<?php

namespace App\Services;

use PragmaRX\Google2FA\Google2FA;
use PragmaRX\Google2FA\Support\Constants;
use PragmaRX\Google2FALaravel\Facade as TwoFactor;

class TwoFactorService
{
    public static function generate($id, $email)
    {
        $google2fa = app(Google2FA::class);
        $google2fa->setAlgorithm(Constants::SHA512);
        $prefix = str_pad($id, 40, 'X');
        $key = $google2fa->generateSecretKey(64, $prefix);
        $domain = parse_url(config('app.url'), PHP_URL_HOST);

        return [
            'key' => $key,
            'qr' => TwoFactor::getQRCodeInline(
                $domain,
                $email,
                $key,
                200
            ),
        ];
    }
}
