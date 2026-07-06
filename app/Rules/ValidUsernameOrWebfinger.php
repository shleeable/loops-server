<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;

class ValidUsernameOrWebfinger implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            $fail('The :attribute must be a valid username or webfinger address.');

            return;
        }

        if (str_starts_with($value, '@')) {
            $value = substr($value, 1);
        }

        if (! str_contains($value, '@')) {
            if ($this->failsUsername($attribute, $value)) {
                $fail('The :attribute is not a valid username.');
            }

            return;
        }

        [$local, $domain] = explode('@', $value, 2);

        if ($this->failsUsername($attribute, $local) || ! $this->validDomain($domain)) {
            $fail('The :attribute is not a valid webfinger address.');
        }
    }

    protected function failsUsername(string $attribute, string $value): bool
    {
        return Validator::make(
            [$attribute => $value],
            [$attribute => [new ValidUsername]]
        )->fails();
    }

    protected function validDomain(string $domain): bool
    {
        return strlen($domain) <= 253
            && str_contains($domain, '.')
            && filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) !== false;
    }
}
