<?php

namespace Database\Factories;

use App\Models\AchievementReport;
use App\Models\GoalPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AchievementReport>
 */
class AchievementReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
    
            return [
            'user_id'=> User::factory(),
            'goal_post_id'=> GoalPost::factory(),
            'title'=>fake()->sentence(),
            'body'=>fake()->text(),
            'image_path'=>fake()->imageUrl(640, 480, 'cats', true),
            'created_at'=>now(),
            'updated_at'=>now()
    
        ];
    }
}
