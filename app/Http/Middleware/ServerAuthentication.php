<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\Contracts\ServerAuthenticationServiceInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ServerAuthentication
{
    public function __construct(
        private readonly ServerAuthenticationServiceInterface $authService,
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->authService->validateRequest($request)) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Invalid or missing server authentication.',
            ], 401);
        }

        $apiKey = $this->authService->getApiKeyFromRequest($request);

        if ($apiKey) {
            $request->attributes->set('api_key', $apiKey);
            $request->attributes->set('game_id', $apiKey->game_id);
        }

        return $next($request);
    }
}
