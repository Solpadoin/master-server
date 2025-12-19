<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\GameServerController;
use App\Http\Controllers\Api\V1\ServerHeartbeatController;
use App\Http\Controllers\Api\V1\SetupController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\Admin\GameController;
use App\Http\Controllers\Api\V1\Admin\InstanceController;

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
        // Game Management
        Route::apiResource('games', GameController::class);

        // Instance Management
        Route::apiResource('instances', InstanceController::class);
        Route::put('/instances/{instance}/schema', [InstanceController::class, 'updateSchema'])
            ->name('api.v1.admin.instances.schema');

        // API Key Management for game servers
        Route::post('/games/{game}/api-keys', [GameController::class, 'generateApiKey'])
            ->name('api.v1.admin.games.api-keys.store');

        Route::delete('/games/{game}/api-keys/{apiKey}', [GameController::class, 'revokeApiKey'])
            ->name('api.v1.admin.games.api-keys.destroy');
    });
});
