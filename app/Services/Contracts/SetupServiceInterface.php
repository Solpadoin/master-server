<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\User;

interface SetupServiceInterface
{
    /**
     * Check if the application has been set up.
     */
    public function isSetupComplete(): bool;

    /**
     * Check if database connection is working.
     */
    public function checkDatabaseConnection(): bool;

    /**
     * Check if Redis connection is working.
     */
    public function checkRedisConnection(): bool;

    /**
     * Get all service health statuses.
     *
     * @return array{database: bool, redis: bool, all_passed: bool}
     */
    public function checkAllServices(): array;

    /**
     * Create the super admin user.
     */
    public function createSuperAdmin(string $email, string $password, ?string $name = null): User;

    /**
     * Mark setup as complete.
     */
    public function markSetupComplete(): void;
}
