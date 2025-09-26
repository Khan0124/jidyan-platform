<?php

namespace App\Services\Media;

use App\Models\PlayerProfile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ChunkedUploadManager
{
    public function __construct(private readonly string $disk = 'media_inbox')
    {
    }

    public function handleChunk(
        PlayerProfile $player,
        UploadedFile $chunk,
        string $uploadUuid,
        int $index,
        int $total,
        string $originalFilename
    ): ?string {
        $disk = Storage::disk($this->disk);
        $chunkDirectory = $player->getKey()."/chunks/{$uploadUuid}";

        $disk->makeDirectory($chunkDirectory);
        $disk->putFileAs($chunkDirectory, $chunk, "{$index}.part");

        if ($index + 1 < $total) {
            return null;
        }

        for ($i = 0; $i < $total; $i++) {
            if (! $disk->exists($chunkDirectory."/{$i}.part")) {
                return null;
            }
        }

        $extension = pathinfo($originalFilename, PATHINFO_EXTENSION) ?: ($chunk->getClientOriginalExtension() ?: 'mp4');
        $finalDirectory = (string) $player->getKey();
        $disk->makeDirectory($finalDirectory);

        $finalRelativePath = $finalDirectory.'/'.$uploadUuid.'.'.$extension;

        if ($disk->exists($finalRelativePath)) {
            $disk->delete($finalRelativePath);
        }

        $finalHandle = fopen($disk->path($finalRelativePath), 'wb');

        try {
            for ($i = 0; $i < $total; $i++) {
                $chunkPath = $disk->path($chunkDirectory."/{$i}.part");
                $chunkHandle = fopen($chunkPath, 'rb');

                try {
                    stream_copy_to_stream($chunkHandle, $finalHandle);
                } finally {
                    fclose($chunkHandle);
                }
            }
        } finally {
            fclose($finalHandle);
            $disk->deleteDirectory($chunkDirectory);
        }

        return $finalRelativePath;
    }
}
