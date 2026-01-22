<?php

namespace App\Services;

use App\Models\AdminSetting;
use Illuminate\Support\Facades\Cache;

class ConfigService
{
    const CACHE_KEY = 'loops:services:config:';

    public function domain()
    {
        return parse_url(config('app.url'), PHP_URL_HOST);
    }

    public function forYouFeed($flush = false)
    {
        $supported = app(RedisService::class)->supportsBloomFilters();
        if (! $supported) {
            return false;
        }

        $key = self::CACHE_KEY.'for-you-feed';

        if ($flush) {
            Cache::forget($key);
        }

        return Cache::rememberForever($key, function () {
            $config = AdminSetting::where('key', 'fyf.enabled')->first();
            if (! $config) {
                app(SettingsFileService::class)->generatePublicConfig();

                return false;
            }

            return (bool) $config->value;
        });
    }

    public function federation($flush = false)
    {
        $key = self::CACHE_KEY.'federation';

        if ($flush) {
            Cache::forget($key);
        }

        return Cache::rememberForever($key, function () {
            $config = AdminSetting::where('key', 'federation.enableFederation')->first();
            if (! $config) {
                app(SettingsFileService::class)->generatePublicConfig();

                return false;
            }

            return (bool) $config->value;
        });
    }

    public function federationMode($flush = false)
    {
        $key = self::CACHE_KEY.'federationMode';

        if ($flush) {
            Cache::forget($key);
        }

        return Cache::rememberForever($key, function () {
            $config = AdminSetting::where('key', 'federation.federationMode')->first();
            if (! $config) {
                app(SettingsFileService::class)->generatePublicConfig();

                return 'lockdown';
            }

            return $config->value;
        });
    }

    public function userSpamDetection($flush = false)
    {
        $key = self::CACHE_KEY.'general:userSpamDetection';

        if ($flush) {
            Cache::forget($key);
        }

        return Cache::rememberForever($key, function () {
            $config = AdminSetting::where('key', 'general.userSpamDetection')->first();
            if (! $config) {
                app(SettingsFileService::class)->generatePublicConfig();

                return [];
            }

            return $config->value;
        });
    }

    public function federationAllowedServers($flush = false)
    {
        $key = self::CACHE_KEY.'federation:lockdown:allowed';

        if ($flush) {
            Cache::forget($key);
        }

        return Cache::rememberForever($key, function () {
            $config = AdminSetting::where('key', 'federation.allowedInstances')->first();
            if (! $config) {
                app(SettingsFileService::class)->generatePublicConfig();

                return [];
            }

            return $config->value;
        });
    }

    public function federationAuthorizedFetch($flush = false)
    {
        $key = self::CACHE_KEY.'federation:lockdown:authorizedFetch';

        if ($flush) {
            Cache::forget($key);
        }

        return Cache::rememberForever($key, function () {
            $config = AdminSetting::where('key', 'federation.authorizedFetch')->first();
            if (! $config) {
                app(SettingsFileService::class)->generatePublicConfig();

                return false;
            }

            return (bool) $config->value;
        });
    }
}
