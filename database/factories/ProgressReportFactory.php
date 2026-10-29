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
            // 報告者は紐づく目標の投稿者と同じユーザーにする
            'user_id' => fn (array $attributes) => GoalPost::find($attributes['goal_post_id'])->user_id,
            'title' => fake()->sentence(),
            'body' => fake()->text(),
            'image_path' => 'https://picsum.photos/seed/'.fake()->uuid().'/640/480',
        ];
    }
}
