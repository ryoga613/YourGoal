<?php

namespace Database\Factories;

use App\Models\AchievementReport;
use App\Models\Comment;
use App\Models\GoalPost;
use App\Models\KnowledgePost;
use App\Models\ProgressReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // 紐づけ先の候補となるファクトリー（または null）を定義
        // ここで「どれか1つだけランダムに選ぶ」仕組みを作ります
        $relations = [
            'knowledge_post_id' => KnowledgePost::factory(),
            'goal_post_id' => GoalPost::factory(),
            'progress_report_id' => ProgressReport::factory(),
            'achievement_report_id' => AchievementReport::factory(),
        ];
        $selectedKey = array_rand($relations);

        return [
            'user_id' => User::factory(),
            'knowledge_post_id' => $selectedKey === 'knowledge_post_id' ? KnowledgePost::factory() : null,
            'goal_post_id' => $selectedKey === 'goal_post_id' ? GoalPost::factory() : null,
            'progress_report_id' => $selectedKey === 'progress_report_id' ? ProgressReport::factory() : null,
            'achievement_report_id' => $selectedKey === 'achievement_report_id' ? AchievementReport::factory() : null,
            'parent_id' => null, // 返信コメントなどで使う場合は必要に応じて調整
            'body' => fake()->text(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
