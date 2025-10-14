<?php

namespace App\Federation\Validators;

abstract class BaseValidator
{
    abstract public function validate(array $activity): bool;

    protected function hasRequiredFields(array $activity, array $fields): bool
    {
        foreach ($fields as $field) {
            if (! isset($activity[$field])) {
                return false;
            }
        }

        return true;
    }

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
