<?php

namespace App\Repositories;

use App\Models\Task;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TaskRepository implements TaskRepositoryInterface
{
    private static int $paginateLimit = 50;

    public function index(int $userId, CarbonImmutable $date): LengthAwarePaginator
    {
        return Task::where([
            'user_id' => $userId,
            'task_date' => $date,
        ])->paginate(self::$paginateLimit);
    }

    public function store(int $userId, CarbonImmutable $date, string $content): Task
    {
        return Task::create([
            'user_id' => $userId,
            'task_date' => $date,
            'content' => $content,
        ]);
    }

    public function update(int $userId, int $taskId, string $content): Task
    {
        $task = Task::findOrFail($taskId);
        $task->updateOrFail(['content' => $content]);
        return $task->fresh();
    }

    public function destroy(int $userId, int $taskId): bool
    {
        $task = Task::findOrFail($taskId);
        $task->deleteOrFail();
        return true;
    }

    // public function dates(int $userId): array
    // {
    //     return Task::where(['user_id' => $userId])
    //         ->distinct()->pluck('task_date')->toArray();
    // }

    public function updatePositions(mixed $data)
    {
        //
    }

    public function findById(int $id): Task
    {
        return Task::findOrFail($id);
    }
}
