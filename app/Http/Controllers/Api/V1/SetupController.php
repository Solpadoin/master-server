<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateSuperAdminRequest;
use App\Services\Contracts\SetupServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SetupController extends Controller
{
    public function __construct(
        private readonly SetupServiceInterface $setupService,
    ) {}

    /**
     * Get current setup status.
     */
    public function status(): JsonResponse
    {
        return response()->json([
            'setup_complete' => $this->setupService->isSetupComplete(),
            'has_admin' => $this->hasAdminUser(),
        ]);
    }

    /**
     * Check all services health.
     */
    public function checkServices(): JsonResponse
    {
        $results = $this->setupService->checkAllServices();

        return response()->json([
            'services' => [
                'database' => [
                    'status' => $results['database'] ? 'ok' : 'error',
                    'message' => $results['database']
                        ? 'Database connection successful'
                        : 'Unable to connect to database',
                ],
                'redis' => [
                    'status' => $results['redis'] ? 'ok' : 'error',
                    'message' => $results['redis']
                        ? 'Redis connection successful'
                        : 'Unable to connect to Redis',
                ],
            ],
            'all_passed' => $results['all_passed'],
            'message' => $results['all_passed']
                ? 'All services are running correctly. You can continue to the next step.'
                : 'Some services are not available. Please check your configuration.',
        ]);
    }

    /**
     * Create super admin user.
     */
    public function createSuperAdmin(CreateSuperAdminRequest $request): JsonResponse
    {
        if ($this->hasAdminUser()) {
            return response()->json([
                'error' => 'Admin Exists',
                'message' => 'A super admin user already exists.',
            ], 400);
        }

        $user = $this->setupService->createSuperAdmin(
            email: $request->input('email'),
            password: $request->input('password'),
            name: $request->input('name'),
        );

        return response()->json([
            'message' => 'Super admin created successfully.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ], 201);
    }

    /**
     * Complete setup and mark as done.
     */
    public function complete(): JsonResponse
    {
        if (! $this->hasAdminUser()) {
            return response()->json([
                'error' => 'No Admin',
                'message' => 'Please create a super admin user first.',
            ], 400);
        }

        $services = $this->setupService->checkAllServices();

        if (! $services['all_passed']) {
            return response()->json([
                'error' => 'Services Not Ready',
                'message' => 'All services must be running before completing setup.',
            ], 400);
        }

        $this->setupService->markSetupComplete();

        return response()->json([
            'message' => 'Setup completed successfully. You can now log in.',
            'redirect' => '/login',
        ]);
    }

    /**
     * Check if an admin user exists.
     */
    private function hasAdminUser(): bool
    {
        try {
            return \App\Models\User::where('is_admin', true)->exists();
        } catch (\Exception) {
            return false;
        }
    }
}
