<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use App\Services\Contracts\GameServerServiceInterface;
use App\Services\Contracts\GameCacheServiceInterface;
use App\Services\Contracts\ServerAuthenticationServiceInterface;
use App\Services\Contracts\GameServiceInterface;
use App\Services\Contracts\InstanceServiceInterface;
use App\Services\Implementations\GameServerService;
use App\Services\Implementations\GameCacheService;
use App\Services\Implementations\ServerAuthenticationService;
use App\Services\Implementations\GameService;
use App\Services\Implementations\InstanceService;
use App\Repositories\Contracts\GameServerRepositoryInterface;
use App\Repositories\Contracts\GameRepositoryInterface;
use App\Repositories\Contracts\InstanceRepositoryInterface;
use App\Repositories\Contracts\ApiKeyRepositoryInterface;
use App\Repositories\Implementations\GameServerRepository;
use App\Repositories\Implementations\GameRepository;
use App\Repositories\Implementations\InstanceRepository;
use App\Repositories\Implementations\ApiKeyRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->registerServices();
        $this->registerRepositories();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Prevent lazy loading in development to catch N+1 issues
        Model::preventLazyLoading(! $this->app->isProduction());

        // Prevent silently discarding attributes
        Model::preventSilentlyDiscardingAttributes(! $this->app->isProduction());
    }

    /**
     * Register service bindings.
     */
    private function registerServices(): void
    {
        $this->app->bind(GameServerServiceInterface::class, GameServerService::class);
        $this->app->bind(GameCacheServiceInterface::class, GameCacheService::class);
        $this->app->bind(ServerAuthenticationServiceInterface::class, ServerAuthenticationService::class);
        $this->app->bind(GameServiceInterface::class, GameService::class);
        $this->app->bind(InstanceServiceInterface::class, InstanceService::class);
    }

    /**
     * Register repository bindings.
     */
    private function registerRepositories(): void
    {
        $this->app->bind(GameServerRepositoryInterface::class, GameServerRepository::class);
        $this->app->bind(GameRepositoryInterface::class, GameRepository::class);
        $this->app->bind(InstanceRepositoryInterface::class, InstanceRepository::class);
        $this->app->bind(ApiKeyRepositoryInterface::class, ApiKeyRepository::class);
    }
}
