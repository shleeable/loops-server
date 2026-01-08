<?php

use App\Models\AdminSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $defaultSettings = [
            [
                'key' => 'media.maxVideoDuration',
                'value' => 180,
                'type' => 'number',
                'is_public' => true,
                'description' => 'Maximum video duration in seconds',
            ],
        ];

        foreach ($defaultSettings as $setting) {
            AdminSetting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'type' => $setting['type'],
                    'is_public' => $setting['is_public'],
                    'description' => $setting['description'],
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
