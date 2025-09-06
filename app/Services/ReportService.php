<?php

namespace App\Services;

class ReportService
{
    const REPORT_VERSION = '0.1';

    const REPORT_TYPES = [
        1010 => 'Inappropriate and irrelevant search',
        1011 => 'Violence, abuse, and criminal exploitation',
        1012 => 'Hate and harassment',
        1013 => 'Suicide and self-harm',
        1014 => 'Disordered eating and unhealthy body image',
        1015 => 'Dangerous activities and challenges',
        1016 => 'Nudity and sexual content',
        1017 => 'Shocking and graphic content',
        1018 => 'Misinformation',
        1019 => 'Deceptive behavior and spam',
        1020 => 'Regulated goods and activities',
        1021 => 'Frauds and scams',
        1022 => 'Sharing personal information',
        1023 => 'Report illegal content',
        1024 => 'Counterfeits and intellectual property',
        1025 => 'Undisclosed branded content',
        1026 => 'Other',
    ];

    public static function getRules()
    {
        return array_map(function ($key, $value) {
            return [
                'key' => (string) $key,
                'message' => trans('reports.types.'.$key),
            ];
        }, array_keys(self::REPORT_TYPES), self::REPORT_TYPES);
    }

    public static function getKeys()
    {
        return array_keys(self::REPORT_TYPES);
    }

    public static function getKeysToString()
    {
        return implode(',', self::getKeys());
    }

    public static function getById($id)
    {
        return self::REPORT_TYPES[$id];
    }
}
