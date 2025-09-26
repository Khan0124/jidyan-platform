<?php

namespace App\Jobs;

use App\Models\PlayerMedia;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class CleanupMediaJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(): void
    {
        PlayerMedia::query()
            ->where('status', 'ready')
            ->whereDate('created_at', '<=', now()->subDays(30))
            ->chunk(100, function ($medias) {
                foreach ($medias as $media) {
                    if ($media->path && Storage::disk('media_inbox')->exists($media->path)) {
                        Storage::disk('media_archive')->put($media->getKey().'.mp4', Storage::disk('media_inbox')->get($media->path));
                        Storage::disk('media_inbox')->delete($media->path);
                    }
                }
            });
    }
}
