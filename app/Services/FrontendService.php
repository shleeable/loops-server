<?php

namespace App\Services;

use Cache;

class FrontendService
{
    public static function getCache()
    {
        $res = Cache::get('settings:public');
        if (! $res) {
            $r = new SettingsFileService;
            $res = $r->generatePublicConfig();
        }

        return $res;
    }

    public static function getCaptchaData()
    {
        if(!config('captcha.enabled')) {
            $res = json_encode([
                'enabled' => false,
                'provider' => null,
                'siteKey' => null
            ], JSON_UNESCAPED_SLASHES);
        } else {
            $res = json_encode([
                'enabled' => true,
                'provider' => config('captcha.driver'),
                'siteKey' => config('captcha.siteKey')
            ], JSON_UNESCAPED_SLASHES);
        }

        return 'window.appCaptcha = ' . $res .';';
    }

    public static function getAppData()
    {
        $res = self::getCache();

        return json_encode($res, JSON_UNESCAPED_SLASHES);
    }

    public static function getAppName()
    {
        $res = self::getCache();

        return data_get($res, 'app.name', 'Loops');
    }

    public static function getAppDescription()
    {
        $res = self::getCache();

        return data_get($res, 'app.description', 'A creative community for sharing short videos and connecting with others.');
    }

    public static function getAppFavicon()
    {
        $res = self::getCache();

        return data_get($res, 'branding.favicon', url('/favicon.png'));
    }
}
