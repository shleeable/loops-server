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
                'url' => $settings['general.instanceUrl'] ?? config('app.url'),
                'description' => $settings['general.instanceDescription'] ?? '',
            ],
            'branding' => [
                'logo' => $settings['branding.logo'] ?? url('/nav-logo.png'),
                'favicon' => $settings['branding.favicon'] ?? url('/favicon.png'),
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

        $config = [
            'version' => now()->timestamp,
            'generated_at' => now()->toISOString(),
            'settings' => $settings,
        ];

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
        return Storage::disk('public')->url('config/app-config.json');
    }
}
