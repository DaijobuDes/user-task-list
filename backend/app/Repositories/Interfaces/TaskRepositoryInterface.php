<?php

namespace App\Repositories\Interfaces;

use App\Models\Task;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TaskRepositoryInterface
{
    public function index(int $userId, CarbonImmutable $date): LengthAwarePaginator;
    public function store(int $userId, CarbonImmutable $date, string $content, int $position): Task;
    public function update(int $userId, int $taskId, string $content, int $isFinished): Task;
    public function destroy(int $userId, int $taskId): bool;
    // public function dates(int $userId): array;
    public function updatePositions(array $data): bool;
    public function findById(int $id): Task;
}
