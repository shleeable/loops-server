<?php

/*
|--------------------------------------------------------------------------
| Trust Proxies Configuration
|--------------------------------------------------------------------------
|
| Configure trusted proxies for applications running behind load balancers
| or reverse proxies. This is especially important for Docker deployments.
|
*/
return [
    'trust_proxies' => [
        'enabled' => env('TRUST_PROXIES_ENABLED', false),
        'proxies' => env('TRUST_PROXIES', '*'),
        'headers' => env('TRUST_PROXY_HEADERS', 'all'),
    ],
];
