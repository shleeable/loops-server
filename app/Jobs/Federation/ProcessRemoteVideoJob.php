<?php

namespace App\Jobs\Federation;

use App\Federation\Audience;
use App\Models\Profile;
use App\Models\Video;
use App\Services\SanitizeService;
use Carbon\Carbon;
use Exception;
use FFMpeg\Format\Video\X264;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Throwable;

class ProcessRemoteVideoJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;

    public $tries = 3;

    public $backoff = [30, 60, 120];

    /**
     * The number of seconds after which the job's unique lock will be released.
     */
    public $uniqueFor = 3600;

    protected int $profileId;

    protected array $object;

    protected array $attachment;

    const STATUS_PROCESSING = 1;

    const STATUS_PUBLISHED = 2;

    const STATUS_FAILED = 3;

    public function __construct(int $profileId, array $object, array $attachment)
    {
        $this->profileId = $profileId;
        $this->object = $object;
        $this->attachment = $attachment;
    }

    /**
     * Get the unique ID for the job based on the ActivityPub object ID.
     */
    public function uniqueId(): string
    {
        $apId = $this->object['id'] ?? 'unknown';

        return 'process-remote-video:'.hash('sha256', $apId);
    }

    public function handle(): void
    {
        $remoteUrl = $this->attachment['url'];
        $apId = $this->object['id'] ?? null;
        $tempPath = null;
        $video = null;

        try {
            if ($apId && Video::where('ap_id', $apId)->exists()) {
                return;
            }

            $profile = Profile::find($this->profileId);
            if (! $profile) {
                throw new Exception("Profile not found: {$this->profileId}");
            }

            $video = $this->initializeVideoModel($profile);

            $tempPath = $this->downloadVideo($remoteUrl);

            $media = FFMpeg::open($tempPath);

            $duration = $media->getDurationInSeconds();
            $sizeKb = (int) round(filesize($tempPath) / 1024);

            $filename = Str::random(40).'.mp4';
            $s3Path = "videos/{$profile->id}/{$video->id}/{$filename}";
            $disk = 's3';

            $media->export()
                ->toDisk($disk)
                ->inFormat(new X264('aac', 'libx264'))
                ->save($s3Path);

            $video->update([
                'vid' => $s3Path,
                'vid_optimized' => $s3Path,
                'size_kb' => $sizeKb,
                'duration' => (int) round($duration),
                'status' => self::STATUS_PUBLISHED,
                'has_processed' => true,
                'alt_text' => isset($this->attachment['name']) && ! empty($this->attachment['name']) ? app(SanitizeService::class)->cleanPlainText($this->attachment['name']) : null,
                'is_sensitive' => ! empty($this->object['sensitive']) ? (bool) $this->object['sensitive'] : false,
            ]);

            if ($video->caption) {
                $video->syncHashtagsFromCaption();
                $video->syncMentionsFromCaption();
            }

        } catch (Throwable $e) {
            $this->handleFailure($e, $video, $remoteUrl);
        } finally {
            if ($tempPath && file_exists($tempPath)) {
                @unlink($tempPath);
            }
        }
    }

    /**
     * Creates the initial video record with 'Processing' status.
     */
    protected function initializeVideoModel(Profile $profile): Video
    {
        $video = new Video;
        $video->profile_id = $profile->id;
        $video->status = self::STATUS_PROCESSING;
        $video->is_local = false;
        $video->has_thumb = false;
        $video->caption = $this->extractContent($this->object);
        $video->visibility = $this->determineVisibilityFromObject($this->object, $profile);
        $video->ap_id = $this->object['id'];
        $video->remote_media_url = $this->attachment['url'];
        $video->created_at = $this->extractPublishedDate($this->object);
        $video->updated_at = now();

        if (isset($this->object['url'])) {
            $video->uri = is_string($this->object['url']) ? $this->object['url'] : null;
        }

        $video->save();

        return $video;
    }

    /**
     * Downloads video to temp directory with security checks.
     */
    protected function downloadVideo(string $url): string
    {
        $this->validateUrlHost($url);

        $tempFilename = Str::random(40).'.mp4';
        $tempStoragePath = 'tmp-removide/'.$tempFilename;

        $tempPath = storage_path('app/private/'.$tempStoragePath);

        $tmpDir = dirname($tempPath);
        if (! is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $maxSize = 100 * 1024 * 1024;

        $fileHandle = fopen($tempPath, 'w+');

        $response = Http::timeout(120)
            ->withHeaders(['User-Agent' => app('user_agent')])
            ->withOptions([
                'sink' => $fileHandle,
                'progress' => function ($downloadTotal, $downloadedBytes) use ($maxSize) {
                    if ($downloadedBytes > $maxSize) {
                        throw new Exception('Download aborted: File exceeds 100MB limit.');
                    }
                },
            ])
            ->get($url);

        if (is_resource($fileHandle)) {
            fclose($fileHandle);
        }

        if (! $response->successful()) {
            @unlink($tempPath);
            throw new Exception('HTTP Error during download: '.$response->status());
        }

        if (! file_exists($tempPath)) {
            throw new Exception("File was not created at: {$tempPath}");
        }

        $mimeType = mime_content_type($tempPath);
        if (! str_starts_with($mimeType, 'video/')) {
            @unlink($tempPath);
            throw new Exception("Invalid mime type: {$mimeType}");
        }

        return $tempPath;
    }

    /**
     * SSRF Protection
     */
    protected function validateUrlHost(string $url): void
    {
        $host = parse_url($url, PHP_URL_HOST);

        if (! $host) {
            throw new Exception('Invalid URL format');
        }

        $ips = gethostbynamel($host);
        if (! $ips) {
            throw new Exception("Could not resolve host: $host");
        }

        foreach ($ips as $ip) {
            if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                throw new Exception("Blocked attempt to access internal IP: $ip");
            }
        }
    }

    /**
     * Logic to handle failures and clean up DB state
     */
    protected function handleFailure(Throwable $e, ?Video $video, string $url): void
    {
        Log::error('ProcessRemoteVideoJob: Failed', [
            'profile_id' => $this->profileId,
            'url' => $url,
            'error' => $e->getMessage(),
            'video_id' => $video?->id,
        ]);

        if ($video) {
            $video->delete();
        }

        throw $e;
    }

    private function determineVisibilityFromObject(array $object, Profile $actor): int
    {
        $to = $object['to'] ?? [];
        $cc = $object['cc'] ?? [];
        if (! is_array($to)) {
            $to = [$to];
        }
        if (! is_array($cc)) {
            $cc = [$cc];
        }

        return Audience::determineVisibility($to, $cc, $actor->getFollowersUrl());
    }

    private function extractContent(array $object): string
    {
        $content = $object['content'] ?? '';

        return app(SanitizeService::class)->cleanHtmlWithSpacing($content);
    }

    private function extractPublishedDate(array $object): Carbon
    {
        return isset($object['published'])
            ? Carbon::parse($object['published'])
            : now();
    }
}
