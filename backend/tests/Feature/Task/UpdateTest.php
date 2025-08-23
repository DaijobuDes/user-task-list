<?php

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('it shows unauthenticated when user is not logged in', function () {
    // Arrange
    $task = Task::factory()->create(['user_id' => $this->user]);
    $expected = [
        'message' => 'Unauthenticated.',
    ];

    // Act
    $response = $this->putJson(route('tasks.update', $task->id));

    // Assert
    $response->assertExactJson($expected);
    $response->assertStatus(401);
    $response->json();
});

test('it cannot update other user\'s tasks', function () {
    // Arrange
    $user = User::factory()->create();
    $task = Task::factory()->create([
        'user_id' => $user,
    ]);
    $payload = [
        'task' => $task->id,
        'date' => now()->format('Y-m-d'),
        'content' => 'This task is updated',
    ];

    $expected = [
        'message' => 'Forbidden',
    ];

    // Act
    $this->actingAs($this->user);
    $response = $this->putJson(route('tasks.update', $payload));

    // Assert
    $response->assertStatus(403);
    $response->assertJsonFragment($expected);
});

test('it should be able to update tasks', function () {
    // Arrange
    $task = Task::factory()->create([
        'user_id' => $this->user,
    ]);
    $payload = [
        'task' => $task->id,
        'date' => now()->format('Y-m-d'),
        'content' => 'This task is updated',
    ];

    // Act
    $this->actingAs($this->user);
    $response = $this->putJson(route('tasks.update', $payload));

    $data = $response->json()['data'];
    $task->refresh();
    unset($payload['date'], $payload['task']);

    // Assert
    $response->assertJsonFragment($payload);
    expect([
        'id' => $data['id'],
        'user_id' => $data['user_id'],
        'content' => $data['content'],
        'is_finished' => $data['is_finished'] ? 1 : 0,
        'position' => $data['position'],
    ])->toBe([
        'id' => $task->id,
        'user_id' => $task->user_id,
        'content' => $task->content,
        'is_finished' => $task->is_finished,
        'position' => $task->position,
    ]);
});

test('it cannot update task due to validation', function () {
    // Arrange
    $task = Task::factory()->create(['user_id' => $this->user]);
    $payload = [
        'task' => $task->id,
        'is_finished' => 'not a boolean',
        'content' => 'This task is a test',
    ];

    $expected = [
        'message' => 'The is finished field must be true or false.',
        'errors' => [
            'is_finished' => [
                'The is finished field must be true or false.'
            ],
        ],
    ];

    // Act
    $this->actingAs($this->user);
    $response = $this->putJson(route('tasks.update', $payload));
    $data = $response->json();

    // Assert
    expect($data)->toBe($expected);
});

test('it updates task positions successfully', function () {
    // Arrange
    $this->actingAs($this->user);

    $tasks = Task::factory()->count(2)->create([
        'user_id' => $this->user->id,
    ]);

    $payload = [
        'items' => [
            ['id' => $tasks[0]->id, 'position' => 1],
            ['id' => $tasks[1]->id, 'position' => 2],
        ],
    ];

    // Act
    $response = $this->putJson(route('tasks.update-positions'), $payload);

    // Assert
    $response->assertOk()->assertJson(['message' => 'Success']);
});

test('it fails when repository returns false', function () {
    // Arrange
    $this->actingAs($this->user);

    $tasks = Task::factory()->count(2)->create([
        'user_id' => $this->user->id,
    ]);

    $payload = [
        'items' => [
            ['id' => $tasks[0]->id, 'position' => 1],
            ['id' => $tasks[1]->id, 'position' => 2],
        ],
    ];

    // Mock repository to force failure
    $this->mock(\App\Repositories\TaskRepository::class, function ($mock) {
        $mock->shouldReceive('updatePositions')->andReturn(false);
    });

    // Act
    $response = $this->putJson(route('tasks.update-positions'), $payload);

    // Assert
    $response->assertStatus(500)
            ->assertJson(['message' => 'Failed to update positions.']);
});

test('it requires items array in request', function () {
    // Arrange
    $this->actingAs($this->user);

    // Act
    $response = $this->putJson(route('tasks.update-positions'), []);

    // Assert
    $response->assertStatus(422);
});
