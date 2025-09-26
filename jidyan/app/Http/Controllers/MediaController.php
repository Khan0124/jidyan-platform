<?php

namespace App\Http\Controllers;

use App\Http\Requests\MediaUploadRequest;
use App\Http\Resources\PlayerMediaResource;
use App\Jobs\ProcessMediaUploadJob;
use App\Models\PlayerMedia;
use App\Services\Media\ChunkedUploadManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    public function store(MediaUploadRequest $request, ChunkedUploadManager $chunkedUploadManager): Response|RedirectResponse|JsonResponse
    {
        $player = $request->user()->playerProfile ?? $request->user()->playerProfile()->create();

        if ($player->media()->where('type', 'video')->count() >= 5) {
            $message = __('Video limit reached');

            if ($request->wantsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return redirect()->back()->withErrors(['file' => $message]);
        }

        $uploadedFile = $request->file('file');
        $originalName = $uploadedFile->getClientOriginalName() ?: 'video.mp4';
        $path = null;

        $chunkTotal = (int) $request->input('chunk_total', 0);
        if ($chunkTotal > 0) {
            $path = $chunkedUploadManager->handleChunk(
                player: $player,
                chunk: $uploadedFile,
                uploadUuid: $request->string('upload_uuid')->toString(),
                index: $request->integer('chunk_index'),
                total: $chunkTotal,
                originalFilename: $request->string('filename')->toString()
            );

            if (! $path) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'status' => 'chunk_received',
                        'upload_uuid' => $request->input('upload_uuid'),
                    ], 202);
                }

                return redirect()->back()->with('status', __('Upload chunk received.'));
            }

            $originalName = $request->string('filename')->toString();
        }

        if (! $path) {
            $filename = Str::uuid().'.'.$uploadedFile->getClientOriginalExtension();
            $path = $uploadedFile->storeAs(
                path: $player->getKey(),
                name: $filename,
                options: ['disk' => 'media_inbox']
            );
        }

        $media = PlayerMedia::create([
            'player_id' => $player->getKey(),
            'type' => 'video',
            'provider' => 'local',
            'path' => $path,
            'status' => 'processing',
            'original_filename' => $originalName,
            'meta' => array_filter([
                'upload_uuid' => $request->input('upload_uuid'),
            ]),
        ]);

        ProcessMediaUploadJob::dispatch($media->id);

        if ($request->wantsJson()) {
            return PlayerMediaResource::make($media->fresh())
                ->response()
                ->setStatusCode(201);
        }

        return redirect()->back()->with('status', __('Upload started.'));
    }

    public function destroy(Request $request, PlayerMedia $media): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $media);
        $media->delete();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'deleted']);
        }

        return back()->with('status', __('Media deleted successfully.'));
    }
}
