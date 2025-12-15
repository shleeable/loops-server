<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\AdminSetting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->unsignedTinyInteger('trust_level')->default(5)->nullable()->index();
        });

        $defaultSettings = [
            [
                'key' => 'fyf.enabled',
                'value' => false,
                'type' => 'boolean',
                'is_public' => true,
                'description' => 'Enable the For You Feed',
            ],

            [
                'key' => 'fyf.freshness_weight',
                'value' => 0.25,
                'type' => 'number',
                'is_public' => false,
                'description' => 'Freshness weight for the FYF Algorithm',
            ],
            [
                'key' => 'fyf.engagement_weight',
                'value' => 0.35,
                'type' => 'number',
                'is_public' => false,
                'description' => 'Engagement weight for the FYF Algorithm',
            ],
            [
                'key' => 'fyf.personalization_weight',
                'value' => 0.30,
                'type' => 'number',
                'is_public' => false,
                'description' => 'Personalization weight for the FYF Algorithm',
            ],
            [
                'key' => 'fyf.creator_quality_weight',
                'value' => 0.10,
                'type' => 'number',
                'is_public' => false,
                'description' => 'Creator quality weight for the FYF Algorithm',
            ],
            [
                'key' => 'fyf.max_age_days',
                'value' => 180,
                'type' => 'number',
                'is_public' => false,
                'description' => 'Max age of content for FYF inclusion',
            ],
            [
                'key' => 'fyf.min_score_threshold',
                'value' => 0.1,
                'type' => 'number',
                'is_public' => false,
                'description' => 'Min score threshold for feed score',
            ],
            [
                'key' => 'fyf.min_results',
                'value' => 5,
                'type' => 'number',
                'is_public' => false,
                'description' => 'Min threshold for personalized results',
            ],
            [
                'key' => 'fyf.discovery_mix_ratio',
                'value' => 0.3,
                'type' => 'number',
                'is_public' => false,
                'description' => 'Discovery mix ratio',
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
                    'version' => 1,
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('trust_level');
        });
    }
};
