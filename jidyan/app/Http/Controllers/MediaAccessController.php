<?php

namespace App\Http\Controllers;

use App\Models\PlayerMedia;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MediaAccessController extends Controller
{
    protected int $ttlMinutes = 10;

    public function show(Request $request, PlayerMedia $media): JsonResponse
    {
        abort_unless($media->isReady(), 404);

        $media->loadMissing('player.user');

        $viewer = $this->resolveViewer($request);

        $this->ensureAuthorized($viewer, $media);

        $expiresAt = now()->addMinutes($this->ttlMinutes);

        $parameters = [
            'media' => $media->getKey(),
            'path' => 'index.m3u8',
        ];

        if ($media->player->visibility !== 'public' && $viewer) {
            $parameters['viewer'] = $viewer->getAuthIdentifier();
        }

        $signedUrl = URL::temporarySignedRoute('media.hls', $expiresAt, $parameters);

        return response()->json([
            'url' => $signedUrl,
            'expires_at' => $expiresAt->toIso8601String(),
        ]);
    }

    public function stream(Request $request, PlayerMedia $media, string $path): Response|BinaryFileResponse
    {
        abort_unless($media->isReady(), 404);

        $media->loadMissing('player');

        $relativePath = trim($path, '/');

        abort_if($relativePath === '' || str_contains($relativePath, '..'), 404);

        $diskPath = $media->getKey().'/'.$relativePath;

        $disk = Storage::disk('media_hls');

        abort_unless($disk->exists($diskPath), 404);

        if (Str::endsWith($relativePath, '.m3u8')) {
            $contents = $disk->get($diskPath);

            return response(
                $this->rewritePlaylist($request, $media, $relativePath, $contents),
                200,
                ['Content-Type' => 'application/vnd.apple.mpegurl']
            );
        }

        $absolutePath = $disk->path($diskPath);

        return response()->file($absolutePath, [
            'Content-Type' => $this->guessMimeType($absolutePath),
        ]);
    }

    protected function rewritePlaylist(Request $request, PlayerMedia $media, string $currentPath, string $contents): string
    {
        $lines = preg_split("/(\r\n|\n|\r)/", $contents);
        $expires = now()->addMinutes($this->ttlMinutes);
        $viewerId = $request->query('viewer');

        foreach ($lines as $index => $line) {
            if ($line === null) {
                continue;
            }

            $trimmed = trim($line);

            if ($trimmed === '') {
                continue;
            }


            if (Str::startsWith($trimmed, '#')) {
                continue;
            }

            $target = $this->normalizeRelativePath($currentPath, $trimmed);

            $lines[$index] = $this->makeSignedUrl($media, $target, $viewerId, $expires);
        }

        return implode("\n", array_map(static fn ($line) => $line ?? '', $lines));
    }

    protected function makeSignedUrl(PlayerMedia $media, string $path, ?string $viewerId, CarbonInterface $expires): string
    {
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $parameters = [
            'media' => $media->getKey(),
            'path' => ltrim($path, '/'),
        ];

        if ($viewerId) {
            $parameters['viewer'] = $viewerId;
        }

        return URL::temporarySignedRoute('media.hls', $expires, $parameters);
    }

    protected function normalizeRelativePath(string $currentPath, string $target): string
    {
        $target = ltrim($target, './');

        if (Str::startsWith($target, '/')) {
            return ltrim($target, '/');
        }

        $directory = trim(dirname($currentPath), '.');

        if ($directory === '' || $directory === DIRECTORY_SEPARATOR) {
            return ltrim($target, '/');
        }

        return trim($directory.'/'.$target, '/');
    }

    protected function ensureAuthorized(?Authenticatable $viewer, PlayerMedia $media): void
    {
        $player = $media->player;

        if ($player->visibility === 'public') {
            return;
        }

        abort_if(! $viewer, 403);

        if ((int) $viewer->getAuthIdentifier() === (int) $player->user_id) {
            return;
        }

        abort_unless(method_exists($viewer, 'hasAnyRole') && $viewer->hasAnyRole([
            'coach',
            'club_admin',
            'agent',
            'verifier',
            'admin',
        ]), 403);
    }

    protected function resolveViewer(Request $request): ?Authenticatable
    {
        return $request->user() ?? $request->user('sanctum');
    }

    protected function guessMimeType(string $path): string
    {
        return match (true) {
            Str::endsWith($path, '.ts') => 'video/mp2t',
            Str::endsWith($path, '.jpg') => 'image/jpeg',
            Str::endsWith($path, '.png') => 'image/png',
            default => mime_content_type($path) ?: 'application/octet-stream',
        };
    }
}
