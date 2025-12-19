<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Instance;
use Illuminate\Support\Collection;

interface InstanceServiceInterface
{
    /**
     * Get all instances for a game.
     *
     * @return Collection<int, Instance>
     */
    public function getByGame(int $gameId): Collection;

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
     * Update instance schema.
     */
    public function updateSchema(Instance $instance, array $schema): Instance;

    /**
     * Delete an instance.
     */
    public function delete(Instance $instance): bool;

    /**
     * Validate data against instance schema.
     *
     * @return array<string, string>
     */
    public function validateData(Instance $instance, array $data): array;
}
