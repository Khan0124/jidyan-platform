<?php

namespace App\Jobs;

use App\Models\PlayerMedia;
use App\Services\Media\FfmpegTranscoder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessMediaUploadJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public int $mediaId)
    {
    }

    public function handle(FfmpegTranscoder $transcoder): void
    {
        $media = PlayerMedia::findOrFail($this->mediaId);
        $transcoder->transcode($media);
    }
}
