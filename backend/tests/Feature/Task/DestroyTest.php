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
    $response = $this->deleteJson(route('tasks.update', $task->id));

    // Assert
    $response->assertExactJson($expected);
    $response->assertStatus(401);
    $response->json();
});

test('it should be able to delete tasks', function () {
    // Arrange
    $task = Task::factory()->create([
        'user_id' => $this->user,
    ]);

    $expected = [
        'message' => 'Success',
    ];

    // Act
    $this->actingAs($this->user);
    $response = $this->deleteJson(route('tasks.destroy', $task->id));

    // Assert
    $response->assertStatus(200);
    $response->assertExactJson($expected);
});

test('it cannot delete other user\'s tasks', function () {
    // Arrange
    $user = User::factory()->create();
    $task = Task::factory()->create([
        'user_id' => $user,
    ]);

    $expected = [
        'message' => 'Forbidden',
    ];

    // Act
    $this->actingAs($this->user);
    $response = $this->deleteJson(route('tasks.destroy', $task->id));

    // Assert
    $response->assertStatus(403);
    $response->assertJsonFragment($expected);
});
