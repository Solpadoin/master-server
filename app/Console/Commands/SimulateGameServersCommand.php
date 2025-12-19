<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\GameInstance;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class SimulateGameServersCommand extends Command
{
    protected $signature = 'game:simulate
                            {--instance= : Specific instance ID to simulate (default: all active instances)}
                            {--servers=25000 : Number of servers to simulate}
                            {--history : Generate 24h historical data}
                            {--clear : Clear existing simulation data first}';

    protected $description = 'Simulate game server activity for testing dashboards';

    public function handle(): int
    {
        $instanceId = $this->option('instance');
        $serverCount = (int) $this->option('servers');
        $generateHistory = $this->option('history');
        $clearFirst = $this->option('clear');

        // Get instances to simulate
        if ($instanceId) {
            $instances = GameInstance::where('id', $instanceId)->get();
            if ($instances->isEmpty()) {
                $this->error("Instance with ID {$instanceId} not found.");

                return self::FAILURE;
            }
        } else {
            $instances = GameInstance::where('is_active', true)->get();
            if ($instances->isEmpty()) {
                $this->warn('No active instances found. Create an instance first.');

                return self::SUCCESS;
            }
        }

        $this->components->info('Simulating game server activity...');

        foreach ($instances as $instance) {
            $this->simulateInstance($instance, $serverCount, $generateHistory, $clearFirst);
        }

        $this->newLine();
        $this->components->success('Simulation complete!');

        return self::SUCCESS;
    }

    private function simulateInstance(
        GameInstance $instance,
        int $serverCount,
        bool $generateHistory,
        bool $clearFirst
    ): void {
        $this->components->task(
            "Simulating {$instance->name} (ID: {$instance->steam_app_id})",
            function () use ($instance, $serverCount, $generateHistory, $clearFirst) {
                if ($clearFirst) {
                    $this->clearInstanceData($instance);
                }

                // Generate active servers
                $this->generateActiveServers($instance, $serverCount);

                // Generate historical data if requested
                if ($generateHistory) {
                    $this->generateHistoricalData($instance);
                }

                return true;
            }
        );
    }

    private function clearInstanceData(GameInstance $instance): void
    {
        $prefix = $instance->getRedisKeyPrefix();
        $keys = Redis::keys("{$prefix}:*");

        if (! empty($keys)) {
            foreach ($keys as $key) {
                // Remove Redis prefix from key name
                $cleanKey = preg_replace('/^[^:]+:/', '', $key) ?: $key;
                Redis::del($cleanKey);
            }
        }
    }

    private function generateActiveServers(GameInstance $instance, int $count): void
    {
        $key = $instance->getServersRedisKey();
        $regions = ['us-east', 'us-west', 'eu-west', 'eu-central', 'asia-pacific'];
        $maps = ['map_001', 'map_002', 'map_003', 'arena_01', 'ctf_main'];

        for ($i = 1; $i <= $count; $i++) {
            $serverId = Str::uuid()->toString();
            $playerCount = rand(0, 32);

            $serverData = [
                'server_id' => $serverId,
                'server_name' => "Server #{$i} - " . $regions[array_rand($regions)],
                'map' => $maps[array_rand($maps)],
                'player_count' => $playerCount,
                'max_players' => 32,
                'peak_players' => rand($playerCount, 32),
                'region' => $regions[array_rand($regions)],
                'ip' => '192.168.1.' . rand(1, 254),
                'port' => 7777 + $i,
                'last_heartbeat' => now()->timestamp,
            ];

            Redis::hset($key, $serverId, json_encode($serverData));
        }
    }

    private function generateHistoricalData(GameInstance $instance): void
    {
        $now = now();

        // Generate data for the last 24 hours
        for ($i = 23; $i >= 0; $i--) {
            $hour = $now->copy()->subHours($i)->format('Y-m-d-H');
            $key = $instance->getStatsRedisKey($hour);

            // Simulate varying activity throughout the day
            // Peak hours around 18:00-22:00
            $hourOfDay = (int) $now->copy()->subHours($i)->format('H');
            $baseServers = 3;
            $basePlayers = 20;

            // Increase activity during evening hours
            if ($hourOfDay >= 18 && $hourOfDay <= 22) {
                $baseServers = rand(8, 15);
                $basePlayers = rand(50, 150);
            } elseif ($hourOfDay >= 12 && $hourOfDay <= 17) {
                $baseServers = rand(5, 10);
                $basePlayers = rand(30, 80);
            } elseif ($hourOfDay >= 0 && $hourOfDay <= 6) {
                $baseServers = rand(1, 4);
                $basePlayers = rand(5, 25);
            } else {
                $baseServers = rand(3, 7);
                $basePlayers = rand(15, 50);
            }

            // Add some randomness
            $activeServers = max(1, $baseServers + rand(-2, 2));
            $peakPlayers = max(1, $basePlayers + rand(-10, 20));

            Redis::hmset($key, [
                'active_servers' => $activeServers,
                'peak_players' => $peakPlayers,
                'recorded_at' => $now->copy()->subHours($i)->toIso8601String(),
            ]);

            // Set TTL to 24 hours from now
            Redis::expire($key, 86400);
        }
    }
}
