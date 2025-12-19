<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\Contracts\SetupServiceInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfSetupComplete
{
    public function __construct(
        private readonly SetupServiceInterface $setupService,
    ) {}

    /**
     * Handle an incoming request.
     * Redirects away from setup if setup is already complete.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->setupService->isSetupComplete()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Setup Already Complete',
                    'message' => 'Application has already been set up.',
                    'redirect' => '/',
                ], 400);
            }

            return redirect('/');
        }

        return $next($request);
    }
}
