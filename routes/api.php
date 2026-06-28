<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\AffiliatesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/health', function () {
    $services = [
        'mysql' => false,
        'redis' => false,
        'worker' => false,
    ];

    try {
        DB::connection()->getPdo();
        $services['mysql'] = true;
    } catch (Throwable) {
        $services['mysql'] = false;
    }

    try {
        Redis::ping();
        $services['redis'] = true;
    } catch (Throwable) {
        $services['redis'] = false;
    }

    $workerLastSeen = Cache::get('queue_worker:last_seen');

    if ($workerLastSeen) {
        $services['worker'] = now()->diffInSeconds($workerLastSeen) <= 30;
    }

    $healthy = collect($services)->every(fn ($status) => $status === true);

    return response()->json([
        'status' => $healthy ? 'ok' : 'error',
        'services' => $services,
        'worker_last_seen' => $workerLastSeen,
        'checked_at' => now()->toISOString(),
    ], $healthy ? 200 : 503);
});

Route::get('/orders/metrics', [OrderController::class, 'metrics']);
Route::get('/orders', [OrderController::class, 'index']);
Route::get('/orders/{id}', [OrderController::class, 'find']);
Route::post('/orders/{id}/status', [OrderController::class, 'status']);
Route::get('/affiliates/{id}/summary',[AffiliatesController::class, 'summary']);
