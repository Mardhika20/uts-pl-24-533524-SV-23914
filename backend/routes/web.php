<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

// Route bawaan Laravel
Route::get('/', function () {
    return view('welcome');
});

// Route sementara untuk pengujian logging stdout
Route::get('/p10-log-test', function () {
    Log::info('p10.stdout.test', [
        'actor' => 'system',
        'action' => 'verify_stdout_logging',
        'reason' => 'configuration_test',
    ]);

    return response()->json([
        'message' => 'Pengujian logging berhasil',
    ]);
});

// Route sementara untuk pengujian status HTTP
Route::get('/p10-status-test', function () {
    return response()->json([
        'message' => 'Pengujian status HTTP',
    ], 418);
});

// P11: Endpoint metrics untuk Prometheus
Route::get('/metrics', function () {
    return response(
        \App\Http\Middleware\TrackHttpRequests::renderMetrics(),
        200,
        [
            'Content-Type' => 'text/plain; version=0.0.4; charset=utf-8',
        ]
    );
});
