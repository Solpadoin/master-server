<?php

declare(strict_types=1);

namespace App\Services\Implementations;

use App\Models\User;
use App\Services\Contracts\SetupServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;

class SetupService implements SetupServiceInterface
{
    private const SETUP_COMPLETE_KEY = 'app_setup_complete';

    public function isSetupComplete(): bool
    {
        // Only check if setup flag file exists
        // Admin user existence is checked separately in the wizard steps
        return file_exists(storage_path('app/.setup_complete'));
    }

    public function checkDatabaseConnection(): bool
    {
        try {
            DB::connection()->getPdo();
            // Try a simple query
            DB::select('SELECT 1');
            return true;
        } catch (\Exception) {
            return false;
        }
    }

    public function checkRedisConnection(): bool
    {
        try {
            Redis::ping();
            return true;
        } catch (\Exception) {
            return false;
        }
    }

    public function checkAllServices(): array
    {
        $database = $this->checkDatabaseConnection();
        $redis = $this->checkRedisConnection();

        return [
            'database' => $database,
            'redis' => $redis,
            'all_passed' => $database && $redis,
        ];
    }

    public function createSuperAdmin(string $email, string $password, ?string $name = null): User
    {
        return User::create([
            'name' => $name ?? 'Super Admin',
            'email' => $email,
            'password' => Hash::make($password),
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);
    }

    public function markSetupComplete(): void
    {
        $setupFile = storage_path('app/.setup_complete');
        file_put_contents($setupFile, now()->toIso8601String());
    }
}
