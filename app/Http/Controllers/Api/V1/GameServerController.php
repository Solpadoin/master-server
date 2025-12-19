<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\DTOs\GameServerFilterDTO;
use App\Http\Controllers\Controller;
use App\Http\Resources\GameServerResource;
use App\Services\Contracts\GameServerServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GameServerController extends Controller
{
    public function __construct(
        private readonly GameServerServiceInterface $gameServerService,
    ) {}

    /**
     * Get list of servers for a game.
     */
    public function index(Request $request, int $gameId): AnonymousResourceCollection
    {
        $filter = GameServerFilterDTO::fromRequest($request, $gameId);
        $servers = $this->gameServerService->getServers($filter);

        return GameServerResource::collection($servers);
    }

    /**
     * Get a specific server.
     */
    public function show(int $gameId, string $serverId): GameServerResource|JsonResponse
    {
        $server = $this->gameServerService->getServer($gameId, $serverId);

        if (! $server) {
            return response()->json([
                'error' => 'Not Found',
                'message' => 'Server not found.',
            ], 404);
        }

        return new GameServerResource($server);
    }
}
