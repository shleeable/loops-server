<?php

namespace App\Jobs\User;

use App\Models\DataExport;
use App\Services\UserDataService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ProcessDataExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;

    public function __construct(
        private DataExport $export
    ) {}

    public function handle(UserDataService $userDataService): void
    {
        try {
            $this->export->update(['status' => 'processing']);

            $user = $this->export->user;
            $exportData = $userDataService->generateDataExport($user, $this->export->type);

            $tempDir = storage_path('app/temp/exports/'.$this->export->id);
            if (! is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $jsonFile = $tempDir.'/data.json';
            file_put_contents($jsonFile, json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            $zipFileName = "export_{$this->export->type}_{$user->id}_{$this->export->id}.zip";
            $zipPath = "exports/{$zipFileName}";
            $fullZipPath = storage_path('app/'.$zipPath);

            $exportsDir = dirname($fullZipPath);
            if (! is_dir($exportsDir)) {
                mkdir($exportsDir, 0755, true);
            }

            $zip = new ZipArchive;
            if ($zip->open($fullZipPath, ZipArchive::CREATE) !== true) {
                throw new \Exception('Could not create ZIP file');
            }

            $zip->addFile($jsonFile, 'data.json');

            if (in_array($this->export->type, ['complete', 'videos'])) {
                $this->addVideoFilesToZip($zip, $user, $tempDir);
            }

            $zip->close();

            $this->cleanupTempFiles($tempDir);

            $fileSize = filesize($fullZipPath);
            $this->export->update([
                'status' => 'ready',
                'file_path' => $zipFileName,
                'file_size' => $fileSize,
                'expires_at' => now()->addDays(7),
            ]);

            sleep(1);

            Cache::forget("acct-data:data-export-history:{$user->id}");
            if (config('logging.dev_log')) {
                Log::info("Data export completed for user {$user->id}, export ID {$this->export->id}");
            }

        } catch (\Exception $e) {
            $this->export->update(['status' => 'failed']);
            if (config('logging.dev_log')) {
                Log::error("Data export failed for export ID {$this->export->id}: ".$e->getMessage());
            }
            throw $e;
        }
    }

    private function addVideoFilesToZip(ZipArchive $zip, $user, string $tempDir): void
    {
        $videos = $user->videos()->get();

        foreach ($videos as $video) {
            if ($video->file_path && Storage::disk('s3')->exists($video->file_path)) {
                $videoPath = Storage::disk('s3')->path($video->file_path);
                $videoName = "videos/{$video->id}_".basename($video->file_path);
                $zip->addFile($videoPath, $videoName);
            }

            if ($video->thumbnail_path && Storage::disk('s3')->exists($video->thumbnail_path)) {
                $thumbnailPath = Storage::disk('s3')->path($video->thumbnail_path);
                $thumbnailName = "thumbnails/{$video->id}_".basename($video->thumbnail_path);
                $zip->addFile($thumbnailPath, $thumbnailName);
            }
        }
    }

    private function cleanupTempFiles(string $tempDir): void
    {
        if (is_dir($tempDir)) {
            $files = glob($tempDir.'/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($tempDir);
        }
    }

    public function failed(\Throwable $exception): void
    {
        $this->export->update(['status' => 'failed']);
        if (config('logging.dev_log')) {
            Log::error("Data export job failed for export ID {$this->export->id}: ".$exception->getMessage());
        }
    }
}
