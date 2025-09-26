<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Throwable;

class HealthCheckController extends Controller
{
    public function __invoke(Request $request): JsonResponse|Response
    {
        $services = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'queue' => $this->checkQueue(),
            'media_disks' => $this->checkMediaDisks(),
        ];

        $httpStatus = collect($services)
            ->every(static fn (array $check): bool => ($check['status'] ?? null) === 'ok')
            ? Response::HTTP_OK
            : Response::HTTP_SERVICE_UNAVAILABLE;

        $payload = [
            'status' => $httpStatus === Response::HTTP_OK ? 'ok' : 'degraded',
            'timestamp' => now()->toIso8601String(),
            'services' => $services,
        ];

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json($payload, $httpStatus);
        }

        return response($payload['status'], $httpStatus)
            ->header('Content-Type', 'text/plain');
    }

    protected function checkDatabase(): array
    {
        try {
            DB::select('select 1 as ok');

            return ['status' => 'ok'];
        } catch (Throwable $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    protected function checkCache(): array
    {
        try {
            $store = Cache::store();
            $key = 'healthcheck:'.uniqid('', true);
            $store->put($key, '1', 5);
            $store->forget($key);

            return [
                'status' => 'ok',
                'store' => $store->getStore()::class,
            ];
        } catch (Throwable $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    protected function checkQueue(): array
    {
        try {
            $connection = Queue::connection();

            return [
                'status' => 'ok',
                'connection' => $connection->getConnectionName() ?? config('queue.default'),
            ];
        } catch (Throwable $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    protected function checkMediaDisks(): array
    {
        $results = [];
        $status = 'ok';

        foreach (['media_inbox', 'media_hls', 'media_archive'] as $disk) {
            try {
                $filesystem = Storage::disk($disk);
                $path = $filesystem->path('');

                if (! is_dir($path) && ! @mkdir($path, 0755, true) && ! is_dir($path)) {
                    throw new \RuntimeException("Unable to initialise disk path: {$path}");
                }

                $results[$disk] = 'ok';
            } catch (Throwable $e) {
                $status = 'error';
                $results[$disk] = $e->getMessage();
            }
        }

        return [
            'status' => $status,
            'disks' => $results,
        ];
    }
}
