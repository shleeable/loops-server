<?php

return [

    /*
    | Master switch for the scheduled sync. The command can still be run
    | manually when this is false.
    */
    'enabled' => env('DENYLIST_SYNC_ENABLED', true),

    'timeout' => (int) env('DENYLIST_SYNC_TIMEOUT', 30),

    'retries' => (int) env('DENYLIST_SYNC_RETRIES', 3),

    /*
    | Create a pre-blocked instance row for listed domains we have never
    | federated with.
    */
    'create_missing' => (bool) env('DENYLIST_SYNC_CREATE_MISSING', true),

    /*
    | Also block subdomains of listed domains. Off by default because it
    | uses naive suffix matching, not the public suffix list.
    */
    'match_subdomains' => (bool) env('DENYLIST_SYNC_MATCH_SUBDOMAINS', false),

    'chunk' => (int) env('DENYLIST_SYNC_CHUNK', 500),

];
