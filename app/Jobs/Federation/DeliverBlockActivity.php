<?php

namespace App\Jobs\Federation;

use App\Federation\ActivityBuilders\BlockActivityBuilder;
use App\Models\Profile;
use App\Services\HttpSignatureService;
use App\Services\SigningService;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeliverBlockActivity implements ShouldBeUnique, ShouldQueue
{
    use Batchable, Queueable;

    public $actor;

    public $targetProfile;

    public $blockId;

    private $devLog;

    private $deliveryTimeout;

    public $tries = 3;

    public $timeout = 120;

    public $uniqueFor = 3600;

    public $deleteWhenMissingModels = true;

    /**
     * Calculate the number of seconds to wait before retrying the job.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [1, 10, 15];
    }

    public function uniqueId(): string
    {
        return "deliver-block-{$this->actor->id}-{$this->targetProfile->id}";
    }

    /**
     * Create a new job instance.
     */
    public function __construct(Profile $actor, Profile $targetProfile, $blockId)
    {
        $this->actor = $actor;
        $this->targetProfile = $targetProfile;
        $this->blockId = $blockId;

        $this->devLog = (bool) config('logging.dev_log');
        $this->deliveryTimeout = config('loops.federation.delivery.timeout', 10);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $actor = $this->actor;
        $targetProfile = $this->targetProfile;
        $blockId = $this->blockId;

        if ($targetProfile->local) {
            $this->devLog && Log::info('Skipping Block delivery for local target', [
                'actor' => $actor->id,
                'target' => $targetProfile->id,
            ]);
            $this->delete();

            return;
        }

        $inboxUrl = $targetProfile->inbox_url;

        if (! $inboxUrl) {
            $this->devLog && Log::warning('Target profile has no inbox URL, skipping Block delivery', [
                'actor' => $actor->id,
                'target' => $targetProfile->id,
            ]);
            $this->delete();

            return;
        }

        $targetActorUrl = $targetProfile->uri ?: $targetProfile->getActorId();

        $activity = BlockActivityBuilder::build($actor, $targetActorUrl, $blockId);

        $parsedUrl = parse_url($inboxUrl);

        $headers = [
            'Host' => $parsedUrl['host'],
            'Date' => now()->toRfc7231String(),
            'Content-Type' => 'application/activity+json',
            'Accept' => 'application/activity+json',
            'User-Agent' => app('user_agent'),
        ];

        $signatureService = app(HttpSignatureService::class);
        $path = $parsedUrl['path'] ?? '/';
        $queryString = isset($parsedUrl['query']) ? '?'.$parsedUrl['query'] : '';
        $requestPath = $path.$queryString;

        try {
            $privateKey = app(SigningService::class)->getPrivateKey();
            $signature = $signatureService->sign(
                $actor->getKeyId(),
                $privateKey,
                $headers,
                'POST',
                $requestPath,
                json_encode($activity)
            );

            $headers['Signature'] = $signature;
        } catch (\Exception $e) {
            $this->devLog && Log::error("Failed to sign request: {$e->getMessage()}", [
                'actor' => $actor->id,
                'inbox' => $inboxUrl,
            ]);

            $this->delete();

            return;
        }

        try {
            $response = Http::timeout($this->deliveryTimeout)
                ->withHeaders($headers)
                ->post($inboxUrl, $activity);

            if ($response->successful()) {
                $this->devLog && Log::info('Activity delivered successfully', [
                    'inbox' => $inboxUrl,
                    'status' => $response->status(),
                ]);

                return;
            }

            if ($response->clientError() && ! in_array($response->status(), [408, 429])) {
                $this->devLog && Log::warning("Permanent client error {$response->status()}", [
                    'inbox' => $inboxUrl,
                ]);
                $this->delete();

                return;
            }

            throw new \Exception("Delivery failed with status {$response->status()}");
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            if ($this->devLog) {
                $error = "Network error: {$e->getMessage()}";
                Log::error($error, ['inbox' => $inboxUrl]);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($this->devLog) {
                Log::error('Error delivering activity', [
                    'inbox' => $inboxUrl,
                    'error' => $e->getMessage(),
                ]);
            }
            throw $e;
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error('Activity delivery job failed permanently', [
            'id' => $this->blockId,
            'actor' => $this->actor->id,
            'target' => $this->targetProfile->id,
            'type' => 'Block',
            'error' => $exception->getMessage(),
        ]);
    }
}
