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
    $expected = [
        'message' => 'Unauthenticated.',
    ];

    // Act
    $response = $this->postJson(route('tasks.store'));

    // Assert
    $response->assertExactJson($expected);
    $response->assertStatus(401);
    $response->json();
});

test('it should be able to store tasks', function () {
    // Arrange
    $payload = [
        'date' => now()->format('Y-m-d'),
        'content' => 'This task is a test',
        'position' => fake()->numberBetween(0, 100),
    ];

    // Act
    $this->actingAs($this->user);
    $response = $this->postJson(route('tasks.store', $payload));
    $data = $response->json()['data'];
    $taskId = $data['id'];
    $task = Task::whereId($taskId)->get()->first();
    unset($payload['date']);

    // Assert
    $response->assertJsonFragment($payload);
    expect([
        'id' => $taskId,
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

test('it cannot store task due to validation', function () {
    // Arrange
    $payload = [
        'date' => 'invalid date',
        'content' => 'This task is a test',
        'position' => fake()->numberBetween(0, 100),
    ];

    $expected = [
        'message' => 'The date field must be a valid date.',
        'errors' => [
            'date' => [
                'The date field must be a valid date.'
            ],
        ],
    ];

    // Act
    $this->actingAs($this->user);
    $response = $this->postJson(route('tasks.store', $payload));
    $data = $response->json();

    // Assert
    expect($data)->toBe($expected);
});
