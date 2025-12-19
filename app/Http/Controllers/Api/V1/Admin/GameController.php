<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateGameRequest;
use App\Http\Requests\UpdateGameRequest;
use App\Http\Resources\ApiKeyResource;
use App\Http\Resources\GameResource;
use App\Models\ApiKey;
use App\Models\Game;
use App\Services\Contracts\GameServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GameController extends Controller
{
    public function __construct(
        private readonly GameServiceInterface $gameService,
    ) {}

    /**
     * Get all games.
     */
    public function index(): AnonymousResourceCollection
    {
        $games = $this->gameService->getAllGames();

        return GameResource::collection($games);
    }

    /**
     * Get a specific game.
     */
    public function show(Game $game): GameResource
    {
        return new GameResource($game->load(['instances', 'apiKeys']));
    }

    /**
     * Create a new game.
     */
    public function store(CreateGameRequest $request): GameResource
    {
        $game = $this->gameService->create($request->validated());

        return new GameResource($game);
    }

    /**
     * Update a game.
     */
    public function update(UpdateGameRequest $request, Game $game): GameResource
    {
        $game = $this->gameService->update($game, $request->validated());

        return new GameResource($game);
    }

    /**
     * Delete a game.
     */
    public function destroy(Game $game): JsonResponse
    {
        $this->gameService->delete($game);

        return response()->json([
            'message' => 'Game deleted successfully.',
        ]);
    }

    /**
     * Generate API key for a game.
     */
    public function generateApiKey(Request $request, Game $game): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $result = $this->gameService->generateApiKey($game, $request->input('name'));

        return response()->json([
            'api_key' => new ApiKeyResource($result['api_key']),
            'secret' => $result['plain_secret'],
            'message' => 'Store this secret securely. It will not be shown again.',
        ], 201);
    }

    /**
     * Revoke an API key.
     */
    public function revokeApiKey(Game $game, ApiKey $apiKey): JsonResponse
    {
        if ($apiKey->game_id !== $game->id) {
            return response()->json([
                'error' => 'Not Found',
                'message' => 'API key not found for this game.',
            ], 404);
        }

        $this->gameService->revokeApiKey($apiKey);

        return response()->json([
            'message' => 'API key revoked successfully.',
        ]);
    }
}
