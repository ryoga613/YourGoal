<?php

namespace Database\Factories;

use App\Models\GoalPost;
use App\Models\ProgressReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProgressReport>
 */
class ProgressReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'goal_post_id' => GoalPost::factory(),
            // レポートの投稿者は目標の投稿者と同じにする
            'user_id' => fn (array $attributes) => GoalPost::find($attributes['goal_post_id'])->user_id,
            'title' => fake()->realText(30),
            'body' => fake()->realText(200),
            'image_path' => null,
        ];
    }
}
