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
