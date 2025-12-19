<?php

declare(strict_types=1);

namespace App\Repositories\Implementations;

use App\Models\Instance;
use App\Repositories\Contracts\InstanceRepositoryInterface;
use Illuminate\Support\Collection;

class InstanceRepository implements InstanceRepositoryInterface
{
    public function findByGameId(int $gameId): Collection
    {
        return Instance::where('game_id', $gameId)
            ->with(['game'])
            ->get();
    }

    public function findById(int $id): ?Instance
    {
        return Instance::with(['game'])->find($id);
    }

    public function create(array $data): Instance
    {
        $instance = Instance::create($data);

        return $instance->load('game');
    }

    public function update(Instance $instance, array $data): Instance
    {
        $instance->update($data);

        return $instance->fresh(['game']);
    }

    public function delete(Instance $instance): bool
    {
        return $instance->delete();
    }
}
