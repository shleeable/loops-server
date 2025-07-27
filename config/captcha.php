<?php

use Illuminate\Support\Str;

return [
    'enabled' => env('LOOPS_CAPTCHA', false) && env('LOOPS_CAPTCHA_DRIVER') && (env('TURNSTILE_SECRET_KEY') || env('HCAPTCHA_SECRET_KEY')),

    'driver' => match (env('LOOPS_CAPTCHA_DRIVER')) {
        'hcaptcha' => 'hcaptcha',
        'turnstile' => 'turnstile',
        default => null,
    },

    'siteKey' => match (env('LOOPS_CAPTCHA_DRIVER')) {
        'hcaptcha' => env('HCAPTCHA_SITE_KEY'),
        'turnstile' => env('TURNSTILE_SITE_KEY'),
        default => null,
    },
];
