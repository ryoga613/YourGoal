<?php

namespace Database\Factories;

use App\Models\GoalPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GoalPost>
 */
class GoalPostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(),
            'body' => fake()->text(),
            'status' => fake()->randomElement(['considering', 'in_progress', 'achieved', 'cancelled']),
            'image_path' => fake()->imageUrl(640, 480, 'cats', true),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
