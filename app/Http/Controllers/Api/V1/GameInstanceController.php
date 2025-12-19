<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateGameInstanceRequest;
use App\Http\Requests\UpdateGameInstanceRequest;
use App\Http\Resources\GameInstanceResource;
use App\Services\Contracts\GameInstanceServiceInterface;
use App\Services\Contracts\GameStatsServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Models\GameInstance;
use Illuminate\Support\Facades\Redis;

class GameInstanceController extends Controller
{
    public function __construct(
        private readonly GameInstanceServiceInterface $gameInstanceService,
        private readonly GameStatsServiceInterface $gameStatsService,
    ) {}

    private const MONITORING_TTL = 300; // 5 minutes

    /**
     * Get monitoring data for all instances (without server details).
     */
    public function monitoring(): JsonResponse
    {
        $instances = GameInstance::all();
        $monitoringData = [];
        $staleThreshold = now()->timestamp - self::MONITORING_TTL;

        foreach ($instances as $instance) {
            $serversKey = $instance->getServersRedisKey();
            $serversData = Redis::hgetall($serversKey);

            $serverCount = 0;
            $totalPlayers = 0;

            foreach ($serversData as $serverJson) {
                $server = json_decode($serverJson, true);

                // Only count servers with heartbeat in last 5 minutes
                if (($server['last_heartbeat'] ?? 0) >= $staleThreshold) {
                    $serverCount++;
                    $totalPlayers += (int) ($server['player_count'] ?? 0);
                }
            }

            $monitoringData[] = [
                'id' => $instance->id,
                'steam_app_id' => $instance->steam_app_id,
                'name' => $instance->name,
                'is_active' => $instance->is_active,
                'server_count' => $serverCount,
                'total_players' => $totalPlayers,
            ];
        }

        return response()->json([
            'data' => $monitoringData,
            'meta' => [
                'ttl_seconds' => self::MONITORING_TTL,
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get paginated servers for a specific instance.
     */
    public function servers(int $id): JsonResponse
    {
        $instance = $this->gameInstanceService->getById($id);

        if (! $instance) {
            return response()->json([
                'error' => 'Not Found',
                'message' => 'Game instance not found.',
            ], 404);
        }

        $page = (int) request()->get('page', 1);
        $perPage = (int) request()->get('per_page', 50);
        $staleThreshold = now()->timestamp - self::MONITORING_TTL;

        $serversKey = $instance->getServersRedisKey();
        $serversData = Redis::hgetall($serversKey);

        // Filter active servers
        $activeServers = [];
        foreach ($serversData as $serverId => $serverJson) {
            $server = json_decode($serverJson, true);

            if (($server['last_heartbeat'] ?? 0) >= $staleThreshold) {
                $activeServers[] = [
                    'server_id' => $serverId,
                    'server_name' => $server['server_name'] ?? 'Unknown',
                    'map' => $server['map'] ?? 'Unknown',
                    'player_count' => (int) ($server['player_count'] ?? 0),
                    'max_players' => (int) ($server['max_players'] ?? 0),
                    'region' => $server['region'] ?? 'Unknown',
                    'ip' => $server['ip'] ?? '',
                    'port' => (int) ($server['port'] ?? 0),
                    'last_heartbeat' => $server['last_heartbeat'] ?? 0,
                    'last_heartbeat_ago' => now()->timestamp - ($server['last_heartbeat'] ?? 0),
                ];
            }
        }

        $total = count($activeServers);
        $totalPages = (int) ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        // Paginate
        $paginatedServers = array_slice($activeServers, $offset, $perPage);

        return response()->json([
            'data' => $paginatedServers,
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
            ],
        ]);
    }

    /**
     * Get dashboard overview stats.
     */
    public function dashboard(): JsonResponse
    {
        $instances = GameInstance::where('is_active', true)->get();
        $totalServers = 0;

        foreach ($instances as $instance) {
            $stats = $this->gameStatsService->getCurrentStats($instance);
            $totalServers += $stats['active_servers'];
        }

        return response()->json([
            'data' => [
                'active_servers' => $totalServers,
                'instances' => GameInstance::count(),
                'active_instances' => GameInstance::where('is_active', true)->count(),
            ],
        ]);
    }

    /**
     * List all game instances.
     */
    public function index(): AnonymousResourceCollection
    {
        $instances = $this->gameInstanceService->getAll();

        return GameInstanceResource::collection($instances);
    }

    /**
     * Create a new game instance.
     */
    public function store(CreateGameInstanceRequest $request): JsonResponse
    {
        $instance = $this->gameInstanceService->create(
            steamAppId: $request->integer('steam_app_id'),
            name: $request->string('name')->toString(),
            memoryLimitMb: $request->integer('memory_limit_mb', 1),
        );

        return response()->json([
            'message' => 'Game instance created successfully.',
            'data' => new GameInstanceResource($instance),
        ], 201);
    }

    /**
     * Get a specific game instance.
     */
    public function show(int $id): JsonResponse
    {
        $instance = $this->gameInstanceService->getById($id);

        if (! $instance) {
            return response()->json([
                'error' => 'Not Found',
                'message' => 'Game instance not found.',
            ], 404);
        }

        return response()->json([
            'data' => new GameInstanceResource($instance),
        ]);
    }

    /**
     * Update a game instance.
     */
    public function update(UpdateGameInstanceRequest $request, int $id): JsonResponse
    {
        $instance = $this->gameInstanceService->getById($id);

        if (! $instance) {
            return response()->json([
                'error' => 'Not Found',
                'message' => 'Game instance not found.',
            ], 404);
        }

        $instance = $this->gameInstanceService->update($instance, $request->validated());

        return response()->json([
            'message' => 'Game instance updated successfully.',
            'data' => new GameInstanceResource($instance),
        ]);
    }

    /**
     * Delete a game instance.
     */
    public function destroy(int $id): JsonResponse
    {
        $instance = $this->gameInstanceService->getById($id);

        if (! $instance) {
            return response()->json([
                'error' => 'Not Found',
                'message' => 'Game instance not found.',
            ], 404);
        }

        $this->gameInstanceService->delete($instance);

        return response()->json([
            'message' => 'Game instance deleted successfully.',
        ]);
    }

    /**
     * Get current stats for a game instance.
     */
    public function stats(int $id): JsonResponse
    {
        $instance = $this->gameInstanceService->getById($id);

        if (! $instance) {
            return response()->json([
                'error' => 'Not Found',
                'message' => 'Game instance not found.',
            ], 404);
        }

        $stats = $this->gameStatsService->getCurrentStats($instance);
        $hourlyStats = $this->gameStatsService->getHourlyStats($instance);

        // Calculate 24h peaks from hourly data
        $peakServers24h = 0;
        $peakPlayers24h = 0;

        foreach ($hourlyStats as $hourData) {
            $peakServers24h = max($peakServers24h, $hourData['active_servers']);
            $peakPlayers24h = max($peakPlayers24h, $hourData['peak_players']);
        }

        // Calculate Redis memory usage for this instance
        $memoryUsed = $this->calculateInstanceMemoryUsage($instance);

        return response()->json([
            'data' => [
                'instance' => new GameInstanceResource($instance),
                'current' => [
                    'active_servers' => $stats['active_servers'],
                    'current_players' => $stats['current_players'],
                    'peak_players' => $peakPlayers24h,
                    'peak_servers' => $peakServers24h,
                ],
                'memory' => [
                    'used_bytes' => $memoryUsed,
                    'used_formatted' => $this->formatBytes($memoryUsed),
                    'limit_bytes' => $instance->memory_limit_mb * 1024 * 1024,
                    'limit_formatted' => $instance->memory_limit_mb . ' MB',
                    'usage_percent' => $instance->memory_limit_mb > 0
                        ? round(($memoryUsed / ($instance->memory_limit_mb * 1024 * 1024)) * 100, 2)
                        : 0,
                ],
                'hourly' => $hourlyStats,
            ],
        ]);
    }

    /**
     * Calculate Redis memory usage for an instance.
     */
    private function calculateInstanceMemoryUsage(GameInstance $instance): int
    {
        $prefix = $instance->getRedisKeyPrefix();
        $totalBytes = 0;

        // Get all keys for this instance (returns keys with Laravel prefix)
        $keys = Redis::keys("{$prefix}:*");

        // Get Predis client for raw commands
        $predis = Redis::connection()->client();

        foreach ($keys as $key) {
            try {
                // Use executeRaw to run MEMORY USAGE command
                // The key from KEYS already includes the Laravel prefix
                $memory = $predis->executeRaw(['MEMORY', 'USAGE', $key]);
                if ($memory !== null) {
                    $totalBytes += (int) $memory;
                }
            } catch (\Exception $e) {
                // Fallback: estimate based on serialized length
                try {
                    // Strip the Laravel prefix to use with Redis facade
                    $redisPrefix = config('database.redis.options.prefix', '');
                    $cleanKey = str_replace($redisPrefix, '', $key);

                    $type = Redis::type($cleanKey);
                    if ($type === 'hash' || $type === 5) {
                        $data = Redis::hgetall($cleanKey);
                        $totalBytes += strlen(serialize($data));
                    } elseif ($type === 'string' || $type === 1) {
                        $data = Redis::get($cleanKey);
                        $totalBytes += strlen($data ?? '');
                    }
                } catch (\Exception $e2) {
                    // Skip this key if we can't read it
                }
            }
        }

        return $totalBytes;
    }

    /**
     * Format bytes to human readable format.
     */
    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' B';
    }
}
