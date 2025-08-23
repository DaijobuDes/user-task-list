<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexTaskRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskPositionRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

class TaskController extends Controller
{
    use AuthorizesRequests;
    private TaskRepositoryInterface $taskRepository;

    public function __construct(TaskRepositoryInterface $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(IndexTaskRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Task::class);

        $validated = $request->validated();
        $userId = auth()->id();

        if (!isset($validated['date'])) {
            $date = CarbonImmutable::now()->startOfDay();
        } else {
            $date = CarbonImmutable::parse($validated['date']);
        }

        $tasks = $this->taskRepository->index($userId, $date);

        return TaskResource::collection($tasks)->response();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $this->authorize('create', Task::class);

        $validated = $request->validated();
        $content = $validated['content'];
        $date = CarbonImmutable::parse($validated['date']);
        $position = $validated['position'] ?? 0;
        $user = auth()->id();

        $task = $this->taskRepository->store($user, $date, $content, $position);

        return (new TaskResource($task))->response()->setStatusCode(201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, int $taskId): JsonResponse
    {
        $task = $this->taskRepository->findById($taskId);
        $this->authorize('update', $task);

        $validated = $request->validated();
        $user = auth()->id();
        $content = $validated['content'];
        $isFinished = $validated['is_finished'];

        $task = $this->taskRepository->update($user, $taskId, $content, $isFinished);

        return (new TaskResource($task))->response()->setStatusCode(200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $taskId): JsonResponse
    {
        $task = $this->taskRepository->findById($taskId);
        $this->authorize('delete', $task);
        $user = auth()->id();

        $this->taskRepository->destroy($user, $taskId);

        return response()->json([
            'message' => 'Success',
        ], 200);
    }

    public function updatePositions(UpdateTaskPositionRequest $request): JsonResponse
    {
        $validated = $request->validated('items');
        $this->authorize('updatePosition', Task::class);

        $status = $this->taskRepository->updatePositions($validated);

        if (!$status) {
            return response()->json([
                'message' => 'Failed to update positions.',
            ], 500);
        }

        return response()->json([
            'message' => 'Success',
        ], 200);
    }
}
