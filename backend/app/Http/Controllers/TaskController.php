<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexTaskRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
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

        return response()->json($this->taskRepository->index($userId, $date), 200);
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
        $user = auth()->id();

        $result = $this->taskRepository->store($user, $date, $content);

        return response()->json([
            'data' => [
                $result
            ],
        ], 201);
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

        $result = $this->taskRepository->update($user, $taskId, $content);

        return response()->json([
            'data' => $result,
        ], 200);
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

    // public function dates(): JsonResponse
    // {
    //     $this->authorize('viewAny', Task::class);
    //     $user = auth()->id();
    //     $dates = $this->taskRepository->dates($user);

    //     return response()->json([
    //         'data' => $dates
    //     ], 200);
    // }
}
