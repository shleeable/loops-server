<?php

namespace App\Console\Commands;

use App\Models\Video;
use Illuminate\Console\Command;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class VideoBackfillDuration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:video-backfill-duration {--limit=10}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate video duration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        $videos = Video::published()->whereNull('duration')->take($limit)->get();

        $this->info('Backfilling for '.$limit.' videos...');
        foreach ($videos as $video) {
            try {
                $media = FFMpeg::fromDisk('s3')->open($video->vid);
                $video->duration = $media->getDurationInSeconds();
                if (! empty($video->caption)) {
                    $video->syncHashtagsFromCaption();
                    $video->syncMentionsFromCaption();
                }
                $video->saveQuietly();
                FFMpeg::cleanupTemporaryFiles();
                $this->info('Backfilled '.$video->id);
            } catch (\Exception $e) {

            }
        }
        $this->info('Finished.');
    }
}
