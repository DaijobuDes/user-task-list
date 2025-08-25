<?php

use App\Models\Task;
use App\Models\User;
use App\Repositories\TaskRepository;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
uses(Tests\TestCase::class);

beforeEach(function () {
    $this->repo = new TaskRepository();
    $this->user = User::factory()->create();
    $this->userId = $this->user->id;
});

it('paginates tasks by user and date', function () {
    $date = CarbonImmutable::today();

    Task::factory()->count(3)->create([
        'user_id' => $this->userId,
        'task_date' => $date,
    ]);

    $tasks = $this->repo->index($this->userId, $date);

    expect($tasks->count())->toBe(3)
        ->and($tasks)->toBeInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class);
});

it('stores a new task', function () {
    $date = CarbonImmutable::today();

    $task = $this->repo->store($this->userId, $date, 'Test Content', 1);

    expect($task)->toBeInstanceOf(Task::class)
        ->and($task->content)->toBe('Test Content')
        ->and($task->position)->toBe(1);
});

it('updates an existing task', function () {
    $task = Task::factory()->create([
        'user_id' => $this->userId,
        'content' => 'Old content',
        'is_finished' => 0,
    ]);

    $updated = $this->repo->update($this->userId, $task->id, 'New content', 1);

    expect($updated->content)->toBe('New content')
        ->and($updated->is_finished)->toBe(1);
});

it('destroys a task', function () {
    $task = Task::factory()->create([
        'user_id' => $this->userId,
    ]);

    $result = $this->repo->destroy($this->userId, $task->id);

    expect($result)->toBeTrue()
        ->and(Task::find($task->id))->toBeNull();
});

it('updates multiple task positions successfully', function () {
    $tasks = Task::factory()->count(2)->create([
        'user_id' => $this->userId,
    ]);

    $data = [
        ['id' => $tasks[0]->id, 'position' => 5],
        ['id' => $tasks[1]->id, 'position' => 10],
    ];

    $result = $this->repo->updatePositions($data);

    expect($result)->toBeTrue()
        ->and($tasks[0]->fresh()->position)->toBe(5)
        ->and($tasks[1]->fresh()->position)->toBe(10);
});

it('returns false if updating positions fails', function () {
    $result = $this->repo->updatePositions([
        ['id' => 9999, 'position' => 1], // non-existing id
    ]);

    expect($result)->toBeFalse();
});

it('finds a task by id', function () {
    $task = Task::factory()->create([
        'user_id' => $this->userId,
    ]);

    $found = $this->repo->findById($task->id);

    expect($found->id)->toBe($task->id);
});

it('searches tasks by content keyword', function () {
    Task::factory()->create([
        'user_id' => $this->userId,
        'content' => 'Buy milk',
    ]);
    Task::factory()->create([
        'user_id' => $this->userId,
        'content' => 'Walk dog',
    ]);

    $results = $this->repo->searchTerm($this->userId, 'milk');

    expect($results->count())->toBe(1)
        ->and($results->first()->content)->toContain('milk');
});
