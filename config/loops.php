<?php

return [
    'feed' => [
        'fyp' => [
            'max_page' => [
                'enabled' => env('LOOPS_FEED_FYP_MAXP_ENABLED', false),
                'max_days' => env('LOOPS_FEED_FYP_MAXP_MAX_DAYS', 7),
            ],
        ],
    ],

    'reports' => [
        'rate_limits' => [
            'admin_exempt' => env('LOOPS_REPORT_ADMIN_EX_LIMIT', true),
            'daily' => env('LOOPS_REPORT_DAILY_LIMIT', 15),
            'monthly' => env('LOOPS_REPORT_MONTHLY_LIMIT', 200),
        ],
    ],

    'uploads' => [
        'rate_limits' => [
            'per_day' => env('LOOPS_UPLOADS_MAX_USER_PER_DAY', false),
        ],
    ],

    'federation' => [
        'delivery' => [
            'timeout' => env('LOOPS_FED_DELIVERY_TIMEOUT', 10),
        ],
    ],

    'registration' => [
        'max_resend_email_verify' => (int) env('LOOPS_REG_MAX_RESEND_EMAIL_VERIFY', 2),
    ],

    'autolinker' => [
        'mentions' => [
            // If true, remove_domain takes precidence over hide_domain
            'remove_domain' => env('LOOPS_AL_MEN_REMOVE_DOMAINS', true),
            'hide_domain' => env('LOOPS_AL_MEN_HIDE_DOMAINS', false),
            'target_blank' => env('LOOPS_AL_MEN_TARGET_BLANK', false),
            'max_length' => env('LOOPS_AL_MEN_MAX_LENGTH', 64),
        ],
    ],
];
