<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Instance;
use Illuminate\Support\Collection;

interface InstanceRepositoryInterface
{
    /**
     * Get instances by game ID.
     *
     * @return Collection<int, Instance>
     */
    public function findByGameId(int $gameId): Collection;

    /**
     * Find instance by ID.
     */
    public function findById(int $id): ?Instance;

    /**
     * Create a new instance.
     */
    public function create(array $data): Instance;

    /**
     * Update an instance.
     */
    public function update(Instance $instance, array $data): Instance;

    /**
     * Delete an instance.
     */
    public function delete(Instance $instance): bool;
}
