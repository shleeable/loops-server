<?php

namespace App\Federation\Validators;

use App\Services\SanitizeService;

class QuoteRequestValidator extends BaseValidator
{
    /**
     * Validates an incoming QuoteRequest activity.
     * QuoteRequest activities are sent to request permission to quote a post.
     *
     * @throws \Exception if the activity is invalid.
     */
    public function validate(array $activity): void
    {
        if (! $this->hasRequiredFields($activity, ['id', 'type', 'actor', 'object', 'instrument'])) {
            throw new \Exception('QuoteRequest activity is missing required fields: id, type, actor, object, or instrument.');
        }

        if ($activity['type'] !== 'QuoteRequest') {
            throw new \Exception("Invalid activity type: expected 'QuoteRequest', got '{$activity['type']}'.");
        }

        if (! is_string($activity['actor'])) {
            throw new \Exception('[QuoteRequestValidator] QuoteRequest activity "actor" must be a string URI.');
        }

        if (! app(SanitizeService::class)->url($activity['actor'], true)) {
            throw new \Exception('[QuoteRequestValidator] Invalid QuoteRequest actor URI.');
        }

        if (! is_string($activity['object'])) {
            throw new \Exception('[QuoteRequestValidator] QuoteRequest activity "object" must be a string URI (the quoted object).');
        }

        if (! app(SanitizeService::class)->url($activity['object'], true)) {
            throw new \Exception('[QuoteRequestValidator] Invalid QuoteRequest object URI.');
        }

        if (! app(SanitizeService::class)->isLocalObject($activity['object'])) {
            throw new \Exception('[QuoteRequestValidator] Invalid QuoteRequest object URI does not reference a local object.');
        }

        $instrument = $activity['instrument'];

        if (is_string($instrument)) {
            if (! app(SanitizeService::class)->url($instrument, true)) {
                throw new \Exception('[QuoteRequestValidator] Invalid QuoteRequest instrument URI.');
            }
        } elseif (is_array($instrument)) {
            if (! isset($instrument['id'])) {
                throw new \Exception('[QuoteRequestValidator] QuoteRequest instrument object must have an "id" field.');
            }

            if (! app(SanitizeService::class)->url($instrument['id'], true)) {
                throw new \Exception('[QuoteRequestValidator] Invalid QuoteRequest instrument ID.');
            }

            if (isset($instrument['quote'])) {
                if (! is_string($instrument['quote'])) {
                    throw new \Exception('[QuoteRequestValidator] Quote post "quote" field must be a string URI.');
                }

                if ($instrument['quote'] !== $activity['object']) {
                    throw new \Exception('[QuoteRequestValidator] Quote post "quote" field must match the QuoteRequest object.');
                }

                if (! app(SanitizeService::class)->isLocalObject($instrument['quote'])) {
                    throw new \Exception('[QuoteRequestValidator] Invalid QuoteRequest object URI does not reference a local object.');
                }
            }
        } else {
            throw new \Exception('[QuoteRequestValidator] QuoteRequest "instrument" must be a string URI or an object.');
        }
    }
}
