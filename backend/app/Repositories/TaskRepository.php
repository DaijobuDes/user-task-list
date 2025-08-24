<?php

namespace App\Repositories;

use App\Models\Task;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

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

    public function store(int $userId, CarbonImmutable $date, string $content, int $position): Task
    {
        return Task::create([
            'user_id' => $userId,
            'task_date' => $date,
            'content' => $content,
            'position' => $position,
        ]);
    }

    public function update(int $userId, int $taskId, string $content, int $isFinished): Task
    {
        $task = Task::findOrFail($taskId);
        $task->updateOrFail(['content' => $content, 'is_finished' => $isFinished]);
        return $task->fresh();
    }

    public function destroy(int $userId, int $taskId): bool
    {
        $task = Task::findOrFail($taskId);
        $task->deleteOrFail();
        return true;
    }

    public function updatePositions(array $data): bool
    {
        try {
            DB::transaction(function () use ($data) {
                foreach ($data as $t) {
                    $status = Task::where(['id' => $t['id']])->update(['position' => $t['position']]);
                    if (!$status) {
                        throw new Exception("Cannot update ID: {$t['id']}");
                    }
                }
            });
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function findById(int $id): Task
    {
        return Task::findOrFail($id);
    }

    public function searchTerm(int $userId, string $word): Collection
    {
        return Task::where(['user_id' => $userId])
            ->whereLike('content', "%{$word}%")
            ->limit(self::$paginateLimit)
            ->get();
    }
}
