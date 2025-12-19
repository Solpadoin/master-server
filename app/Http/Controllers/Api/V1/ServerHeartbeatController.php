<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\DTOs\GameServerDTO;
use App\DTOs\ServerHeartbeatDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterServerRequest;
use App\Http\Requests\ServerHeartbeatRequest;
use App\Http\Resources\GameServerResource;
use App\Services\Contracts\GameServerServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServerHeartbeatController extends Controller
{
    public function __construct(
        private readonly GameServerServiceInterface $gameServerService,
    ) {}

    /**
     * Register a new server.
     */
    public function register(RegisterServerRequest $request): GameServerResource
    {
        $gameId = $request->attributes->get('game_id');

        $serverDTO = GameServerDTO::fromArray(
            array_merge($request->validated(), ['game_id' => $gameId])
        );

        $server = $this->gameServerService->registerServer($serverDTO);

        return new GameServerResource($server);
    }

    /**
     * Process server heartbeat.
     */
    public function heartbeat(ServerHeartbeatRequest $request): GameServerResource|JsonResponse
    {
        $gameId = $request->attributes->get('game_id');

        try {
            $heartbeat = ServerHeartbeatDTO::fromRequest($request);
            $server = $this->gameServerService->processHeartbeat($gameId, $heartbeat);

            return new GameServerResource($server);
        } catch (\RuntimeException $e) {
            return response()->json([
                'error' => 'Not Found',
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Unregister a server.
     */
    public function unregister(Request $request, string $serverId): JsonResponse
    {
        $gameId = $request->attributes->get('game_id');

        $result = $this->gameServerService->unregisterServer($gameId, $serverId);

        if (! $result) {
            return response()->json([
                'error' => 'Not Found',
                'message' => 'Server not found.',
            ], 404);
        }

        return response()->json([
            'message' => 'Server unregistered successfully.',
        ]);
    }
}
