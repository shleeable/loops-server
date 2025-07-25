<?php

namespace App\Services;

use App\Models\AdminSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SettingsFileService
{
    protected $publicDisk;

    protected $privateDisk;

    public function __construct()
    {
        $this->publicDisk = Storage::disk('public');
        $this->privateDisk = Storage::disk('local');
    }

    /**
     * Generate public settings file (safe for frontend consumption)
     */
    public function generatePublicConfig(): string
    {
        $settings = AdminSetting::getPublicSettings();

        $config = [
            'version' => now()->timestamp,
            'generated_at' => now()->toISOString(),
            'app' => [
                'name' => $settings['general.instanceName'] ?? config('app.name'),
                'url' => $settings['general.instanceUrl'] ?? config('app.url'),
                'description' => $settings['general.instanceDescription'] ?? '',
            ],
            'branding' => [
                'logo' => $settings['branding.logo'] ?? null,
                'favicon' => $settings['branding.favicon'] ?? null,
            ],
            'registration' => [
                'open' => $settings['general.openRegistration'] ?? true,
                'email_verification' => $settings['general.emailVerification'] ?? true,
            ],
            'media' => [
                'max_video_size' => $settings['media.maxVideoSize'] ?? 100,
                'max_video_duration' => $settings['media.maxVideoDuration'] ?? 300,
                'allowed_video_formats' => $settings['media.allowedVideoFormats'] ?? ['mp4', 'webm'],
            ],
            'federation' => [
                'enabled' => $settings['federation.enableFederation'] ?? false,
                'mode' => $settings['federation.federationMode'] ?? 'open',
            ],
        ];

        $json = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $this->publicDisk->put('config/app-config.json', $json);

        Cache::put('settings:public', $config, now()->addHours(24));

        return $json;
    }

    /**
     * Generate admin settings file (includes sensitive data)
     */
    public function generateAdminConfig(): string
    {
        $settings = AdminSetting::getAllSettings();

        $config = [
            'version' => now()->timestamp,
            'generated_at' => now()->toISOString(),
            'settings' => $settings,
        ];

        $json = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        Cache::put('settings:admin', $config, now()->addHours(24));

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

        if ($this->publicDisk->exists('config/app-config.json')) {
            $json = $this->publicDisk->get('config/app-config.json');
            $config = json_decode($json, true);

            if ($config) {
                Cache::put('settings:public', $config, now()->addHours(24));

                return $config;
            }
        }

        return json_decode($this->generatePublicConfig(), true);
    }

    /**
     * Get admin config (from cache or DB fallback)
     */
    public function getAdminConfig(): array
    {
        $config = Cache::get('settings:admin');
        if ($config) {
            return $config;
        }

        return json_decode($this->generateAdminConfig(), true);
    }

    /**
     * Get CDN URL for public config
     */
    public function getPublicConfigUrl(): string
    {
        return $this->publicDisk->url('config/app-config.json');
    }
}
