<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\GameServerController;
use App\Http\Controllers\Api\V1\ServerHeartbeatController;
use App\Http\Controllers\Api\V1\SetupController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\GameInstanceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| API Version 1 Routes
| Prefix: /api/v1
|
*/

Route::prefix('v1')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Setup Routes (Only available when setup is incomplete)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['setup.incomplete'])->prefix('setup')->group(function () {
        Route::get('/status', [SetupController::class, 'status'])
            ->name('api.v1.setup.status');

        Route::post('/admin', [SetupController::class, 'createSuperAdmin'])
            ->name('api.v1.setup.admin');

        Route::get('/check-services', [SetupController::class, 'checkServices'])
            ->name('api.v1.setup.check-services');

        Route::post('/complete', [SetupController::class, 'complete'])
            ->name('api.v1.setup.complete');
    });

    /*
    |--------------------------------------------------------------------------
    | Authentication Routes
    |--------------------------------------------------------------------------
    */
    Route::post('/auth/login', [AuthController::class, 'login'])
        ->name('api.v1.auth.login');

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/auth/user', [AuthController::class, 'user'])
            ->name('api.v1.auth.user');

        Route::post('/auth/logout', [AuthController::class, 'logout'])
            ->name('api.v1.auth.logout');
    });

    /*
    |--------------------------------------------------------------------------
    | Public Client Routes (Read-Only)
    |--------------------------------------------------------------------------
    | These routes are for game clients to fetch server information.
    | Rate limited and scoped to specific games.
    */
    Route::middleware(['throttle:api', 'game.scope'])->group(function () {
        Route::get('/games/{gameId}/servers', [GameServerController::class, 'index'])
            ->name('api.v1.servers.index');

        Route::get('/games/{gameId}/servers/{serverId}', [GameServerController::class, 'show'])
            ->name('api.v1.servers.show');
    });

    /*
    |--------------------------------------------------------------------------
    | Server Routes (Authenticated Game Servers)
    |--------------------------------------------------------------------------
    | These routes are for game servers to register and send heartbeats.
    | Authenticated via HMAC signature.
    */
    Route::middleware(['throttle:server', 'server.auth'])->prefix('servers')->group(function () {
        Route::post('/register', [ServerHeartbeatController::class, 'register'])
            ->name('api.v1.servers.register');

        Route::post('/heartbeat', [ServerHeartbeatController::class, 'heartbeat'])
            ->name('api.v1.servers.heartbeat');

        Route::delete('/{serverId}', [ServerHeartbeatController::class, 'unregister'])
            ->name('api.v1.servers.unregister');
    });

    /*
    |--------------------------------------------------------------------------
    | Admin Routes (Authenticated Admin Users)
    |--------------------------------------------------------------------------
    | These routes are for admin panel operations.
    | Protected by Sanctum authentication.
    */
    Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
        // Dashboard Overview
        Route::get('/dashboard', [GameInstanceController::class, 'dashboard'])
            ->name('api.v1.admin.dashboard');

        // Monitoring
        Route::get('/monitoring', [GameInstanceController::class, 'monitoring'])
            ->name('api.v1.admin.monitoring');

        // Game Instance Management
        Route::get('/instances', [GameInstanceController::class, 'index'])
            ->name('api.v1.admin.instances.index');

        Route::post('/instances', [GameInstanceController::class, 'store'])
            ->name('api.v1.admin.instances.store');

        Route::get('/instances/{id}', [GameInstanceController::class, 'show'])
            ->name('api.v1.admin.instances.show');

        Route::put('/instances/{id}', [GameInstanceController::class, 'update'])
            ->name('api.v1.admin.instances.update');

        Route::delete('/instances/{id}', [GameInstanceController::class, 'destroy'])
            ->name('api.v1.admin.instances.destroy');

        // Game Instance Stats (Dashboard data)
        Route::get('/instances/{id}/stats', [GameInstanceController::class, 'stats'])
            ->name('api.v1.admin.instances.stats');
    });
});
