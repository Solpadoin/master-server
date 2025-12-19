<?php

declare(strict_types=1);

namespace App\Services\Implementations;

use App\Models\Instance;
use App\Repositories\Contracts\InstanceRepositoryInterface;
use App\Services\Contracts\InstanceServiceInterface;
use Illuminate\Support\Collection;

class InstanceService implements InstanceServiceInterface
{
    public function __construct(
        private readonly InstanceRepositoryInterface $instanceRepository,
    ) {}

    public function getByGame(int $gameId): Collection
    {
        return $this->instanceRepository->findByGameId($gameId);
    }

    public function findById(int $id): ?Instance
    {
        return $this->instanceRepository->findById($id);
    }

    public function create(array $data): Instance
    {
        return $this->instanceRepository->create($data);
    }

    public function update(Instance $instance, array $data): Instance
    {
        return $this->instanceRepository->update($instance, $data);
    }

    public function updateSchema(Instance $instance, array $schema): Instance
    {
        $this->validateSchemaStructure($schema);

        return $this->instanceRepository->update($instance, ['schema' => $schema]);
    }

    public function delete(Instance $instance): bool
    {
        return $this->instanceRepository->delete($instance);
    }

    public function validateData(Instance $instance, array $data): array
    {
        return $instance->validateData($data);
    }

    /**
     * Validate the schema structure itself.
     *
     * @throws \InvalidArgumentException
     */
    private function validateSchemaStructure(array $schema): void
    {
        foreach ($schema as $index => $field) {
            if (! isset($field['name'])) {
                throw new \InvalidArgumentException("Field at index {$index} is missing 'name' property.");
            }

            if (! isset($field['type'])) {
                throw new \InvalidArgumentException("Field '{$field['name']}' is missing 'type' property.");
            }

            $validTypes = ['string', 'integer', 'float', 'boolean', 'array'];
            if (! in_array($field['type'], $validTypes, true)) {
                throw new \InvalidArgumentException(
                    "Field '{$field['name']}' has invalid type '{$field['type']}'. Valid types: " . implode(', ', $validTypes)
                );
            }
        }
    }
}
