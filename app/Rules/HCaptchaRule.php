<?php

namespace App\Rules;

use App\Services\CaptchaService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class HCaptchaRule implements ValidationRule
{
    public function __construct(
        private CaptchaService $captchaService
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->captchaService->verifyHCaptcha($value)) {
            $fail('The :attribute verification failed. Please try again.');
        }
    }
}
