<?php

namespace App\Services\Media;

use App\Events\MediaProcessingCompleted;
use App\Jobs\TranscodeHlsJob;
use App\Models\PlayerMedia;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class FfmpegTranscoder
{
    public const QUALITIES = [
        '240p' => ['scale' => 240, 'bitrate' => '400k'],
        '360p' => ['scale' => 360, 'bitrate' => '800k'],
        '480p' => ['scale' => 480, 'bitrate' => '1200k'],
        '720p' => ['scale' => 720, 'bitrate' => '2500k'],
    ];

    /**
     * Dispatch a dedicated job for each rendition so they can be processed
     * independently by Horizon workers. The root directory is created up-front
     * to avoid race conditions when the parallel jobs spin up.
     */
    public function transcode(PlayerMedia $media): void
    {
        $rootPath = Storage::disk('media_hls')->path($media->id);

        if (! is_dir($rootPath)) {
            mkdir($rootPath, 0755, true);
        }

        foreach (array_keys(self::QUALITIES) as $quality) {
            TranscodeHlsJob::dispatch($media->id, $quality);
        }
    }

    public function transcodeQuality(PlayerMedia $media, string $quality): void
    {
        if (! array_key_exists($quality, self::QUALITIES)) {
            return;
        }

        $config = self::QUALITIES[$quality];
        $sourcePath = Storage::disk('media_inbox')->path($media->path);
        $targetDir = Storage::disk('media_hls')->path($media->id.'/'.$quality);
        if (! is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $segmentPattern = $targetDir.'/seg_%04d.ts';
        $playlistPath = $targetDir.'/index.m3u8';
        $posterPath = Storage::disk('media_hls')->path($media->id.'/poster.jpg');

        $scale = $config['scale'];
        $bitrate = $config['bitrate'];

        $process = Process::path(dirname($sourcePath))->timeout(600)->run([
            'ffmpeg',
            '-i', $sourcePath,
            '-vf', "scale=-2:{$scale}",
            '-profile:v', 'main',
            '-c:v', 'h264',
            '-preset', 'veryfast',
            '-crf', '23',
            '-b:v', $bitrate,
            '-c:a', 'aac',
            '-b:a', '128k',
            '-hls_time', '4',
            '-hls_playlist_type', 'vod',
            '-hls_segment_filename', $segmentPattern,
            $playlistPath,
        ]);

        if (! $process->successful()) {
            $this->markFailed($media, $quality, $process->errorOutput());

            return;
        }

        if (! file_exists($posterPath)) {
            Process::run([
                'ffmpeg',
                '-i', $sourcePath,
                '-ss', '00:00:01.000',
                '-vframes', '1',
                $posterPath,
            ]);
        }

        $this->registerSuccessfulRendition($media, $quality, $playlistPath, $posterPath, $bitrate, $scale);
    }

    protected function registerSuccessfulRendition(
        PlayerMedia $media,
        string $quality,
        string $playlistPath,
        string $posterPath,
        string $bitrate,
        int $height
    ): void {
        $relativePlaylist = $this->relativePath($playlistPath);
        $relativePoster = $this->relativePath($posterPath);

        $bandwidth = (int) preg_replace('/\D/', '', $bitrate) * 1000;

        $dispatchedEvent = false;

        DB::transaction(function () use ($media, $quality, $relativePlaylist, $relativePoster, $bandwidth, $height, &$dispatchedEvent) {
            $locked = PlayerMedia::lockForUpdate()->find($media->getKey());

            if (! $locked) {
                return;
            }

            $meta = $locked->meta ?? [];
            $renditions = Arr::get($meta, 'renditions', []);
            $renditions[$quality] = [
                'playlist' => $relativePlaylist,
                'bandwidth' => $bandwidth,
                'resolution' => $this->resolutionForHeight($height),
            ];

            $meta['renditions'] = $renditions;

            $updates = [
                'meta' => $meta,
            ];

            if (! $locked->poster_path) {
                $updates['poster_path'] = $relativePoster;
            }

            $allComplete = count($renditions) === count(self::QUALITIES);

            if ($allComplete) {
                $masterPlaylistPath = $this->writeMasterPlaylist($media->getKey(), $renditions);
                $updates['status'] = 'ready';
                $updates['hls_path'] = $masterPlaylistPath;
                $dispatchedEvent = true;
            }

            $locked->forceFill($updates)->save();
        });

        if ($dispatchedEvent) {
            MediaProcessingCompleted::dispatch($media->fresh());
        }
    }

    protected function markFailed(PlayerMedia $media, string $quality, string $errorOutput): void
    {
        DB::transaction(function () use ($media, $quality, $errorOutput) {
            $locked = PlayerMedia::lockForUpdate()->find($media->getKey());

            if (! $locked) {
                return;
            }

            $meta = $locked->meta ?? [];
            $failures = Arr::get($meta, 'failures', []);
            $failures[$quality] = trim($errorOutput);
            $meta['failures'] = $failures;

            $locked->forceFill([
                'status' => 'failed',
                'meta' => $meta,
            ])->save();
        });
    }

    protected function writeMasterPlaylist(int $mediaId, array $renditions): string
    {
        $root = Storage::disk('media_hls')->path($mediaId);
        $qualities = array_keys(self::QUALITIES);

        $playlist = collect($renditions)
            ->sortBy(fn ($data, $quality) => array_search($quality, $qualities))
            ->map(function ($rendition) {
                return implode("\n", [
                    '#EXT-X-STREAM-INF:BANDWIDTH='.$rendition['bandwidth'].',RESOLUTION='.$rendition['resolution'],
                    basename(dirname($rendition['playlist'])).'/'.basename($rendition['playlist']),
                ]);
            })
            ->prepend('#EXTM3U')
            ->implode("\n");

        file_put_contents($root.'/index.m3u8', $playlist);

        return 'media/hls/'.$mediaId.'/index.m3u8';
    }

    protected function relativePath(string $absolutePath): string
    {
        $storagePath = Storage::disk('media_hls')->path('');

        return ltrim(str_replace($storagePath, 'media/hls/', $absolutePath), '/');
    }

    protected function resolutionForHeight(int $height): string
    {
        $width = match ($height) {
            240 => 426,
            360 => 640,
            480 => 854,
            720 => 1280,
            default => 1280,
        };

        return $width.'x'.$height;
    }
}
