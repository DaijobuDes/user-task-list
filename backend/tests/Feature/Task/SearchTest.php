<?php

use App\Models\User;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('it returns tasks that match the search term', function () {
    // Arrange
    $this->actingAs($this->user);

    $matchingTask = Task::factory()->create([
        'user_id' => $this->user->id,
        'content' => 'Test task feature tests',
    ]);

    $nonMatchingTask = Task::factory()->create([
        'user_id' => $this->user->id,
        'content' => 'Something unrelated',
    ]);

    // Act
    $response = $this->getJson(route('tasks.search', [
        'term' => 'feature',
    ]));

    // Assert
    $response->assertOk();
    $response->assertJsonFragment([
        'id' => $matchingTask->id,
        'content' => $matchingTask->content,
    ]);

    $response->assertJsonMissing([
        'id' => $nonMatchingTask->id,
    ]);
});

test('it requires a search term', function () {
    // Arrange
    $this->actingAs($this->user);

    // Act
    $response = $this->getJson(route('tasks.search'));

    // Assert
    $response->assertStatus(422);
});

test('it denies access to guests', function () {
    // Act
    $response = $this->getJson(route('tasks.search', [
        'term' => 'Pest',
    ]));

    // Assert
    $response->assertStatus(401);
});

test('it returns forbidden if user is not authorized', function () {
    // Arrange: mock policy to deny
    $this->actingAs($this->user);

    $this->mock(\App\Policies\TaskPolicy::class, function ($mock) {
        $mock->shouldReceive('search')->andReturn(false);
    });

    // Act
    $response = $this->getJson(route('tasks.search', [
        'term' => 'Pest',
    ]));

    // Assert
    $response->assertStatus(403);
});
