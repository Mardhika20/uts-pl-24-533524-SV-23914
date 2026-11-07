<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;
use Illuminate\Support\Facades\Log;

class HealthController extends Controller
{
    public function index(): JsonResponse
    {
        $checks = [
            'database' => 'unhealthy',
            'application_data' => 'unhealthy',
            'cache' => 'unhealthy',
        ];

        try {
            $connection = DB::connection();

            $host = $connection->getConfig('host') ?: 'db';
            $port = (int) ($connection->getConfig('port') ?: 3306);

            // Cek apakah port database dapat dihubungi.
            // Maksimal menunggu 1 detik.
            $socket = @stream_socket_client(
                "tcp://{$host}:{$port}",
                $errno,
                $errstr,
                1
            );

            if ($socket !== false) {
                fclose($socket);

                // Verifikasi koneksi database sebenarnya.
                DB::select('SELECT 1');
                $checks['database'] = 'healthy';

                // Verifikasi data dasar aplikasi.
                try {
                    DB::table('recipes')
                        ->select('id')
                        ->limit(1)
                        ->get();

                    $checks['application_data'] = 'healthy';
                } catch (Throwable $e) {
                    // Tetap unhealthy.
                }
            }
        } catch (Throwable $e) {
            // Database tidak dapat dihubungi.
        }

        // Jangan mengakses cache berbasis database
        // apabila database sedang mati.
        $cacheUsesDatabase = config('cache.default') === 'database';

        if (!$cacheUsesDatabase || $checks['database'] === 'healthy') {
            try {
                $key = 'health_check_' . Str::random(16);

                Cache::put($key, 'ok', 60);

                if (Cache::get($key) === 'ok') {
                    $checks['cache'] = 'healthy';
                }

                Cache::forget($key);
            } catch (Throwable $e) {
                // Cache tetap unhealthy.
            }
        }

        $failedComponents = [];

        foreach ($checks as $component => $status) {
            if ($status !== 'healthy') {
                $failedComponents[] = $component;
            }
        }

        $healthy = empty($failedComponents);

        if (!$healthy) {
        Log::error('health.check.failed', [
            'actor' => 'system',
            'action' => 'health_check',
            'reason' => in_array('database', $failedComponents, true)
                ? 'database_unavailable'
                : 'health_component_failed',
            'failed_components' => $failedComponents,
        ]);
        }

        return response()->json([
            'status' => $healthy ? 'healthy' : 'unhealthy',
            'checks' => $checks,
            'failed_components' => $failedComponents,
        ], $healthy ? 200 : 503)
        ->header('Cache-Control', 'no-store');
    }
}


