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
     * 対象の投稿IDはCHECK制約によりちょうど1つだけ値が入っている必要があるため、
     * デフォルトは目標投稿に紐づけ、for〇〇() で対象を切り替える。
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'knowledge_post_id' => null,
            'goal_post_id' => GoalPost::factory(),
            'progress_report_id' => null,
            'achievement_report_id' => null,
            'parent_id' => null,
            'body' => fake()->realText(100),
        ];
    }

    public function forGoalPost(?GoalPost $goalPost = null): static
    {
        return $this->target('goal_post_id', $goalPost ?? GoalPost::factory());
    }

    public function forKnowledgePost(?KnowledgePost $knowledgePost = null): static
    {
        return $this->target('knowledge_post_id', $knowledgePost ?? KnowledgePost::factory());
    }

    public function forProgressReport(?ProgressReport $progressReport = null): static
    {
        return $this->target('progress_report_id', $progressReport ?? ProgressReport::factory());
    }

    public function forAchievementReport(?AchievementReport $achievementReport = null): static
    {
        return $this->target('achievement_report_id', $achievementReport ?? AchievementReport::factory());
    }

    /**
     * 親コメントへの返信にする。返信は親コメントと同じ投稿に紐づける。
     */
    public function replyTo(Comment $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'knowledge_post_id' => $parent->knowledge_post_id,
            'goal_post_id' => $parent->goal_post_id,
            'progress_report_id' => $parent->progress_report_id,
            'achievement_report_id' => $parent->achievement_report_id,
            'parent_id' => $parent->id,
        ]);
    }

    /**
     * 指定したカラムだけに値を入れ、他の対象カラムは null にする。
     */
    protected function target(string $column, mixed $value): static
    {
        return $this->state(fn (array $attributes) => array_merge([
            'knowledge_post_id' => null,
            'goal_post_id' => null,
            'progress_report_id' => null,
            'achievement_report_id' => null,
        ], [$column => $value]));
    }
}
