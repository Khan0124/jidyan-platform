<?php

namespace App\Jobs;

use App\Models\PlayerMedia;
use App\Services\Media\FfmpegTranscoder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TranscodeHlsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public int $mediaId, public string $quality)
    {
    }

    public function handle(FfmpegTranscoder $transcoder): void
    {
        $media = PlayerMedia::findOrFail($this->mediaId);
        $transcoder->transcodeQuality($media, $this->quality);
    }
}
