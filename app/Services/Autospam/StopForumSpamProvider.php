<?php

namespace App\Services\Autospam;

use RuntimeException;

class StopForumSpamProvider extends BaseDataProvider
{
    protected string $name = 'stopforumspam';

    private string $apiUrl = 'https://api.stopforumspam.com/api';

    public function checkIp(string $ip): array
    {
        return $this->performCheck('ip', $ip);
    }

    public function checkEmail(string $email): array
    {
        return $this->performCheck('email', $email);
    }

    public function checkUrl(string $url): array
    {
        return [
            'result' => false,
            'confidence' => 0,
            'category' => 'not_applicable',
            'metadata' => [],
        ];
    }

    public function checkIpAndEmail(string $ip, string $email): array
    {
        $type = 'ip_and_email';
        $value = $ip.':'.$email;

        return $this->getCached($type, $value, function () use ($ip, $email) {
            $encodedIp = urlencode($ip);
            $encodedEmail = urlencode($email);
            $url = $this->apiUrl."?ip={$encodedIp}&email={$encodedEmail}&json";
            $response = $this->http->get($url);

            if (! $response->successful()) {
                // if API is unresponsive, return a negative result to prevent false positives.
                return [
                    'result' => false,
                ];
            }

            $data = $response->json();

            $ipAppears = data_get($data, 'ip.appears', 0);
            $emailAppears = data_get($data, 'email.appears', 0);

            return [
                'result' => (bool) ($ipAppears || $emailAppears),
            ];
        });
    }

    private function performCheck(string $type, string $value): array
    {
        return $this->getCached($type, $value, function () use ($type, $value) {
            $encodedValue = urlencode($value);
            $url = $this->apiUrl."?{$type}={$encodedValue}&json";
            $response = $this->http->get($url);

            if (! $response->successful()) {
                throw new RuntimeException('StopForumSpam API request failed');
            }

            $data = $response->json();
            $typeData = $data[$type] ?? [];

            return [
                'result' => (bool) ($typeData['appears'] ?? false),
            ];
        });
    }

    protected function getCacheTtl(): int
    {
        return 3600;
    }
}
