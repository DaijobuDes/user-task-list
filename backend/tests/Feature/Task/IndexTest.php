<?php

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();

    for ($i = -5; $i < 5; $i++) {
        Task::factory()->create([
            'user_id' => $this->user,
            'task_date' => now()->addDays($i)->format('Y-m-d'),
            'position' => fake()->numberBetween(0, 100),
        ]);
    }

});

test('it shows unauthenticated when user is not logged in', function () {
    // Arrange
    $expected = [
        'message' => 'Unauthenticated.',
    ];

    // Act
    $response = $this->getJson(route('tasks.index'));

    // Assert
    $response->assertExactJson($expected);
    $response->assertStatus(401);
    $response->json();
});

test('it should be able to show their tasks', function () {
    // Arrange
    $task = Task::factory()->create([
        'user_id' => $this->user,
        'task_date' => now()->format('Y-m-d'),
        'position' => fake()->numberBetween(0, 100),
    ]);

    $expected = [
        'id' => $task->id,
        'user_id' => $task->user_id,
        'content' => $task->content,
        'is_finished' => $task->is_finished,
        'position' => $task->position,
    ];

    // Act
    $this->actingAs($this->user);
    $response = $this->getJson(route('tasks.index'));

    // Assert
    $response->assertJsonFragment($expected);
    $response->assertStatus(200);
});


test('it can filter tasks based on date', function () {
    // Arrange
    $date = now()->addDays(10)->format('Y-m-d');
    $task = Task::factory()->create([
        'user_id' => $this->user,
        'task_date' => $date,
        'position' => fake()->numberBetween(0, 100),
    ]);

    $expected = [
        'id' => $task->id,
        'user_id' => $task->user_id,
        'content' => $task->content,
        'is_finished' => $task->is_finished,
        'position' => $task->position,
    ];

    // Act
    $this->actingAs($this->user);
    $response = $this->getJson(route('tasks.index', [
        'date' => $date,
    ]));

    // Assert
    $response->assertJsonFragment($expected);
    $response->assertStatus(200);
});
