<?php

namespace App\Federation\Handlers;

abstract class BaseHandler
{
    public function localDomain(): string
    {
        $app = parse_url(config('app.url'));
        $appHost = strtolower(data_get($app, 'host'));

        return $appHost;
    }

    public function isLocalObject(string $url): bool
    {
        $u = parse_url($url);

        if (! $u) {
            return false;
        }

        if ($u['scheme'] != 'https') {
            return false;
        }

        $host = strtolower(data_get($u, 'host'));
        $appHost = $this->localDomain();

        if ($host === $appHost) {
            return true;
        }

        return false;
    }
}
