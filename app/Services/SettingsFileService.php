<?php

namespace App\Services;

use App\Models\AdminSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SettingsFileService
{
    public function flush()
    {
        $this->generatePublicConfig();
        $this->generateAdminConfig();
        app(ConfigService::class)->federation(true);
        app(ConfigService::class)->federationMode(true);
        app(ConfigService::class)->federationAllowedServers(true);
        app(ConfigService::class)->federationAuthorizedFetch(true);
    }

    /**
     * Generate public settings file (safe for frontend consumption)
     */
    public function generatePublicConfig(): string
    {
        $settings = AdminSetting::getPublicSettings();

        $config = [
            'app' => [
                'name' => $settings['general.instanceName'] ?? config('app.name'),
                'url' => config('app.url'),
                'description' => $settings['general.instanceDescription'] ?? '',
            ],
            'branding' => [
                'logo' => $settings['branding.logo'] ?? '/nav-logo.png',
                'favicon' => $settings['branding.favicon'] ?? '/favicon.png',
                'customCSS' => $settings['branding.customCSS'] ?? false,
            ],
            'media' => [
                'max_video_size' => $settings['media.maxVideoSize'] ?? 100,
                'max_video_duration' => $settings['media.maxVideoDuration'] ?? 300,
                'allowed_video_formats' => $settings['media.allowedVideoFormats'] ?? ['mp4'],
            ],
            'registration' => $settings['general.openRegistration'] ?? false,
            'federation' => $settings['federation.enableFederation'] ?? false,
        ];

        $json = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        Cache::put('settings:public', $config);

        return $json;
    }

    /**
     * Generate admin settings file (includes sensitive data)
     */
    public function generateAdminConfig(): string
    {
        $settings = AdminSetting::getAllSettings();

        $config = $settings;

        $json = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        Cache::put('settings:admin', $config);

        return $json;
    }

    /**
     * Get public config (from cache, file, or DB fallback)
     */
    public function getPublicConfig(): array
    {
        $config = Cache::get('settings:public');
        if ($config) {
            return $config;
        }

        return json_decode($this->generatePublicConfig(), true);
    }

    /**
     * Get admin config (from cache or DB fallback)
     */
    public function getAdminConfig(): array
    {
        return json_decode($this->generateAdminConfig(), true);
    }

    /**
     * Get CDN URL for public config
     */
    public function getPublicConfigUrl(): string
    {
        return Storage::disk('public')->url('config/app-config.json');
    }
}
