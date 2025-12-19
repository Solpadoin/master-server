<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\Contracts\GameServiceInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GameScopeMiddleware
{
    public function __construct(
        private readonly GameServiceInterface $gameService,
    ) {}

    /**
     * Handle an incoming request.
     *
     * Validates that the game exists and is active.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $gameId = $request->route('gameId');

        if (! $gameId) {
            return response()->json([
                'error' => 'Bad Request',
                'message' => 'Game ID is required.',
            ], 400);
        }

        $game = $this->gameService->findBySteamAppId((int) $gameId);

        if (! $game) {
            return response()->json([
                'error' => 'Not Found',
                'message' => 'Game not found.',
            ], 404);
        }

        if (! $game->is_active) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'Game is not active.',
            ], 403);
        }

        $request->attributes->set('game', $game);
        $request->attributes->set('game_id', $game->steam_app_id);

        return $next($request);
    }
}
