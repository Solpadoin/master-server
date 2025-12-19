<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\Contracts\SetupServiceInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSetupComplete
{
    public function __construct(
        private readonly SetupServiceInterface $setupService,
    ) {}

    /**
     * Handle an incoming request.
     * Redirects to setup wizard if setup is not complete.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->setupService->isSetupComplete()) {
            // Allow setup routes
            if ($request->is('setup*') || $request->is('api/v1/setup*')) {
                return $next($request);
            }

            // Redirect to setup for web requests
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Setup Required',
                    'message' => 'Application setup is not complete.',
                    'redirect' => '/setup',
                ], 503);
            }

            return redirect('/setup');
        }

        return $next($request);
    }
}
