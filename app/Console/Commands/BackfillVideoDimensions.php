<?php

namespace App\Console\Commands;

use App\Models\Video;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class BackfillVideoDimensions extends Command
{
    protected $signature = 'videos:backfill-dimensions {--chunk=200}';

    protected $description = 'Populate missing width/height on videos using ffprobe metadata';

    public function handle(): int
    {
        $this->info('Populate missing width/height on videos using ffprobe metadata');

        if (! $this->confirm('Do you wish to continue?')) {
            $this->error('aborting...');
            exit;
        }
        $query = Video::query()
            ->whereNotNull('vid_optimized')
            ->where('is_local', true)
            ->whereNull('width');

        $bar = $this->output->createProgressBar($query->count());
        $bar->start();

        $query->lazyById((int) $this->option('chunk'))->each(function (Video $video) use ($bar) {
            try {
                [$width, $height] = $this->probe($video);

                $video->forceFill(['width' => $width, 'height' => $height])->saveQuietly();
            } catch (\Throwable $e) {

            } finally {
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();

        return self::SUCCESS;
    }

    protected function probe(Video $video): array
    {
        $url = Storage::disk('s3')->url($video->vid_optimized);

        $process = new Process([
            'ffprobe',
            '-v', 'error',
            '-select_streams', 'v:0',
            '-show_entries', 'stream=width,height:stream_side_data=rotation',
            '-of', 'json',
            $url,
        ]);

        $process->setTimeout(30);
        $process->mustRun();

        $stream = json_decode($process->getOutput(), true)['streams'][0] ?? null;

        if (! $stream) {
            throw new \RuntimeException('No video stream found');
        }

        $width = (int) $stream['width'];
        $height = (int) $stream['height'];

        $rotation = abs((int) ($stream['side_data_list'][0]['rotation'] ?? 0));
        if (in_array($rotation, [90, 270], true)) {
            [$width, $height] = [$height, $width];
        }

        return [$width, $height];
    }
}
