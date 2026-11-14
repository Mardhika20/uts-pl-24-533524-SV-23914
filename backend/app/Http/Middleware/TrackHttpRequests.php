<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackHttpRequests
{
    private static function metricFile(): string
    {
        return storage_path('app/http_request_counts.json');
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->path() !== 'metrics') {
            $this->record(
                $request->method(),
                $response->getStatusCode()
            );
        }

        return $response;
    }

    private function record(string $method, int $status): void
    {
        $file = fopen(self::metricFile(), 'c+');

        if ($file === false) {
            return;
        }

        try {
            if (!flock($file, LOCK_EX)) {
                return;
            }

            rewind($file);
            $counts = json_decode(stream_get_contents($file), true);

            if (!is_array($counts)) {
                $counts = [];
            }

            $key = $method . ':' . $status;
            $counts[$key] = ($counts[$key] ?? 0) + 1;

            rewind($file);
            ftruncate($file, 0);
            fwrite($file, json_encode($counts));
            fflush($file);
        } finally {
            flock($file, LOCK_UN);
            fclose($file);
        }
    }

    public static function renderMetrics(): string
    {
        $counts = [];
        $file = @fopen(self::metricFile(), 'r');

        if ($file !== false) {
            try {
                if (flock($file, LOCK_SH)) {
                    $counts = json_decode(
                        stream_get_contents($file),
                        true
                    ) ?: [];
                }
            } finally {
                flock($file, LOCK_UN);
                fclose($file);
            }
        }

        $lines = [
            '# HELP http_requests_total Total permintaan HTTP RecipeBox',
            '# TYPE http_requests_total counter',
        ];

        foreach ($counts as $key => $count) {
            [$method, $status] = explode(':', $key, 2);

            $lines[] = sprintf(
                'http_requests_total{method="%s",status="%s"} %d',
                $method,
                $status,
                $count
            );
        }

        return implode("\n", $lines) . "\n";
    }
}
