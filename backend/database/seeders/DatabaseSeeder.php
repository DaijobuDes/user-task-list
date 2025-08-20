<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '1234',
        ]);

        for ($i = -5; $i <= 5; $i++) {
            $date = now()->addDays($i)->startOfDay();

            Task::factory(10)->create([
                'user_id' => $user,
                'task_date' => $date,
                'content' => fake()->text(),
                'is_finished' => fake()->boolean(33),
                'position' => 0,
            ]);
        }
    }
}
