<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\GameInstance;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class StressTestCommand extends Command
{
    protected $signature = 'game:stress-test
                            {--fill-mb=5 : Target size in MB to fill}
                            {--instance= : Instance ID to fill (default: first instance)}
                            {--create-instances=5 : Number of random instances to create}
                            {--skip-fill : Skip filling data, only create instances}';

    protected $description = 'Stress test: fill Redis with data and create random instances';

    private array $gameNames = [
        'Stellar Conquest',
        'Neon Warfare',
        'Cyber Hunters',
        'Phantom Brigade',
        'Iron Legion',
        'Shadow Protocol',
        'Arctic Storm',
        'Desert Strike',
        'Ocean Fury',
        'Mountain Siege',
    ];

    private array $regions = ['us-east', 'us-west', 'eu-west', 'eu-central', 'asia-pacific', 'oceania', 'south-america'];
    private array $maps = ['map_alpha', 'map_bravo', 'map_charlie', 'map_delta', 'arena_01', 'arena_02', 'ctf_main', 'deathmatch_01'];

    public function handle(): int
    {
        $this->components->info('Starting stress test...');

        // Create random instances
        if (! $this->option('skip-fill')) {
            $this->createRandomInstances((int) $this->option('create-instances'));
        }

        // Fill data into instance
        if (! $this->option('skip-fill')) {
            $this->fillInstanceData((int) $this->option('fill-mb'));
        }

        $this->newLine();
        $this->components->success('Stress test complete!');

        return self::SUCCESS;
    }

    private function createRandomInstances(int $count): void
    {
        if ($count <= 0) {
            return;
        }

        $this->components->info("Creating {$count} random instances...");

        $createdCount = 0;
        $usedIds = GameInstance::pluck('steam_app_id')->toArray();

        for ($i = 0; $i < $count; $i++) {
            // Generate random Steam App ID (between 100000 and 9999999)
            do {
                $steamAppId = rand(100000, 9999999);
            } while (in_array($steamAppId, $usedIds));

            $usedIds[] = $steamAppId;

            $name = $this->gameNames[array_rand($this->gameNames)] . ' ' . rand(1, 99);

            $instance = GameInstance::create([
                'steam_app_id' => $steamAppId,
                'name' => $name,
                'memory_limit_mb' => rand(1, 10),
                'is_active' => true,
            ]);

            $this->components->task(
                "Created: {$name} (ID: {$steamAppId})",
                fn () => true
            );

            // Add some random servers to each new instance
            $serverCount = rand(2, 8);
            $this->generateServersForInstance($instance, $serverCount);

            $createdCount++;
        }

        $this->components->info("Created {$createdCount} instances with random servers.");
    }

    private function fillInstanceData(int $targetMb): void
    {
        $instanceId = $this->option('instance');

        if ($instanceId) {
            $instance = GameInstance::find($instanceId);
        } else {
            $instance = GameInstance::first();
        }

        if (! $instance) {
            $this->error('No instance found to fill data.');
            return;
        }

        $this->components->info("Filling {$targetMb} MB into: {$instance->name} (Steam ID: {$instance->steam_app_id})");

        $targetBytes = $targetMb * 1024 * 1024;
        $currentBytes = 0;
        $serverCount = 0;
        $batchSize = 100;

        $key = $instance->getServersRedisKey();

        // Clear existing data first
        Redis::del($key);

        $progressBar = $this->output->createProgressBar($targetMb);
        $progressBar->setFormat(' %current%/%max% MB [%bar%] %percent:3s%% - %message%');
        $progressBar->setMessage('Starting...');
        $progressBar->start();

        while ($currentBytes < $targetBytes) {
            $batch = [];

            for ($i = 0; $i < $batchSize && $currentBytes < $targetBytes; $i++) {
                $serverId = Str::uuid()->toString();
                $serverData = $this->generateLargeServerData($serverCount);

                $jsonData = json_encode($serverData);
                $dataSize = strlen($jsonData) + strlen($serverId) + 50; // overhead

                $batch[$serverId] = $jsonData;
                $currentBytes += $dataSize;
                $serverCount++;
            }

            if (! empty($batch)) {
                Redis::hmset($key, $batch);
            }

            $currentMb = round($currentBytes / 1024 / 1024, 2);
            $progressBar->setProgress(min((int) $currentMb, $targetMb));
            $progressBar->setMessage("{$serverCount} servers, {$currentMb} MB");
        }

        $progressBar->finish();
        $this->newLine(2);

        // Calculate actual memory usage
        $actualBytes = $this->calculateRedisMemory($instance);
        $actualMb = round($actualBytes / 1024 / 1024, 2);

        $this->table(
            ['Metric', 'Value'],
            [
                ['Target Size', "{$targetMb} MB"],
                ['Data Written', round($currentBytes / 1024 / 1024, 2) . ' MB'],
                ['Redis Memory Used', "{$actualMb} MB"],
                ['Servers Created', number_format($serverCount)],
                ['Memory Limit', "{$instance->memory_limit_mb} MB"],
                ['Usage %', round(($actualBytes / ($instance->memory_limit_mb * 1024 * 1024)) * 100, 2) . '%'],
            ]
        );
    }

    private function generateServersForInstance(GameInstance $instance, int $count): void
    {
        $key = $instance->getServersRedisKey();

        for ($i = 0; $i < $count; $i++) {
            $serverId = Str::uuid()->toString();
            $playerCount = rand(0, 32);

            $serverData = [
                'server_id' => $serverId,
                'server_name' => "Server #{$i} - " . $this->regions[array_rand($this->regions)],
                'map' => $this->maps[array_rand($this->maps)],
                'player_count' => $playerCount,
                'max_players' => 32,
                'peak_players' => rand($playerCount, 32),
                'region' => $this->regions[array_rand($this->regions)],
                'ip' => '192.168.' . rand(1, 254) . '.' . rand(1, 254),
                'port' => 7777 + $i,
                'last_heartbeat' => now()->timestamp,
            ];

            Redis::hset($key, $serverId, json_encode($serverData));
        }
    }

    private function generateLargeServerData(int $index): array
    {
        $playerCount = rand(0, 64);

        // Generate extra data to increase size
        $extraData = [
            'metadata' => Str::random(200),
            'settings' => [
                'game_mode' => ['deathmatch', 'capture_flag', 'domination', 'survival'][rand(0, 3)],
                'difficulty' => ['easy', 'normal', 'hard', 'nightmare'][rand(0, 3)],
                'allow_spectators' => (bool) rand(0, 1),
                'max_ping' => rand(50, 200),
                'min_level' => rand(0, 50),
                'password_protected' => (bool) rand(0, 1),
                'anti_cheat_enabled' => true,
                'voice_chat' => (bool) rand(0, 1),
            ],
            'players' => $this->generatePlayerList($playerCount),
            'mods' => $this->generateModList(rand(0, 5)),
            'tags' => ['competitive', 'casual', 'ranked', 'custom', 'event'],
            'description' => Str::random(300),
        ];

        return [
            'server_id' => Str::uuid()->toString(),
            'server_name' => "Stress Test Server #{$index} - " . $this->regions[array_rand($this->regions)],
            'map' => $this->maps[array_rand($this->maps)],
            'player_count' => $playerCount,
            'max_players' => 64,
            'peak_players' => rand($playerCount, 64),
            'region' => $this->regions[array_rand($this->regions)],
            'ip' => rand(1, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(1, 254),
            'port' => rand(7777, 27999),
            'last_heartbeat' => now()->timestamp,
            'extra' => $extraData,
        ];
    }

    private function generatePlayerList(int $count): array
    {
        $players = [];
        for ($i = 0; $i < min($count, 10); $i++) {
            $players[] = [
                'id' => Str::uuid()->toString(),
                'name' => 'Player_' . Str::random(8),
                'score' => rand(0, 10000),
                'ping' => rand(10, 150),
                'team' => rand(0, 1),
            ];
        }
        return $players;
    }

    private function generateModList(int $count): array
    {
        $mods = [];
        for ($i = 0; $i < $count; $i++) {
            $mods[] = [
                'id' => rand(100000, 999999),
                'name' => 'Mod_' . Str::random(10),
                'version' => rand(1, 5) . '.' . rand(0, 9) . '.' . rand(0, 9),
            ];
        }
        return $mods;
    }

    private function calculateRedisMemory(GameInstance $instance): int
    {
        $prefix = $instance->getRedisKeyPrefix();
        $totalBytes = 0;

        $keys = Redis::keys("{$prefix}:*");

        foreach ($keys as $key) {
            $cleanKey = preg_replace('/^[^:]+:/', '', $key) ?: $key;

            try {
                $memory = Redis::command('MEMORY', ['USAGE', $cleanKey]);
                if ($memory !== null) {
                    $totalBytes += (int) $memory;
                }
            } catch (\Exception $e) {
                $type = Redis::type($cleanKey);
                if ($type === 'hash') {
                    $data = Redis::hgetall($cleanKey);
                    $totalBytes += strlen(serialize($data));
                }
            }
        }

        return $totalBytes;
    }
}
