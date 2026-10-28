<?php

namespace Database\Factories;

use App\Enums\GoalPostStatus;
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
            'title' => fake()->realText(30),
            'body' => fake()->realText(200),
            'status' => fake()->randomElement(GoalPostStatus::cases()),
            'image_path' => null,
        ];
    }

    public function considering(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => GoalPostStatus::Considering,
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => GoalPostStatus::InProgress,
        ]);
    }

    public function achieved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => GoalPostStatus::Achieved,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => GoalPostStatus::Cancelled,
        ]);
    }
}
