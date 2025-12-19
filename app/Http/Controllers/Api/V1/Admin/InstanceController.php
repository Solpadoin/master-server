<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateInstanceRequest;
use App\Http\Requests\UpdateInstanceRequest;
use App\Http\Requests\UpdateInstanceSchemaRequest;
use App\Http\Resources\InstanceResource;
use App\Models\Instance;
use App\Services\Contracts\InstanceServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InstanceController extends Controller
{
    public function __construct(
        private readonly InstanceServiceInterface $instanceService,
    ) {}

    /**
     * Get all instances (optionally filtered by game).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $gameId = $request->query('game_id');

        if ($gameId) {
            $instances = $this->instanceService->getByGame((int) $gameId);
        } else {
            $instances = Instance::with(['game'])->get();
        }

        return InstanceResource::collection($instances);
    }

    /**
     * Get a specific instance.
     */
    public function show(Instance $instance): InstanceResource
    {
        return new InstanceResource($instance->load('game'));
    }

    /**
     * Create a new instance.
     */
    public function store(CreateInstanceRequest $request): InstanceResource
    {
        $instance = $this->instanceService->create($request->validated());

        return new InstanceResource($instance);
    }

    /**
     * Update an instance.
     */
    public function update(UpdateInstanceRequest $request, Instance $instance): InstanceResource
    {
        $instance = $this->instanceService->update($instance, $request->validated());

        return new InstanceResource($instance);
    }

    /**
     * Update instance schema.
     */
    public function updateSchema(UpdateInstanceSchemaRequest $request, Instance $instance): InstanceResource|JsonResponse
    {
        try {
            $instance = $this->instanceService->updateSchema($instance, $request->input('schema'));

            return new InstanceResource($instance);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'error' => 'Validation Error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete an instance.
     */
    public function destroy(Instance $instance): JsonResponse
    {
        $this->instanceService->delete($instance);

        return response()->json([
            'message' => 'Instance deleted successfully.',
        ]);
    }
}
