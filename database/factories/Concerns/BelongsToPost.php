<?php

namespace Database\Factories\Concerns;

use App\Models\AchievementReport;
use App\Models\GoalPost;
use App\Models\KnowledgePost;
use App\Models\ProgressReport;

/**
 * 4種類の投稿のうち、いずれか1つだけに紐づくモデル（Comment / PostLike）用のFactoryトレイト
 */
trait BelongsToPost
{
    /**
     * 投稿モデルと外部キーの対応
     */
    protected const POST_FOREIGN_KEYS = [
        KnowledgePost::class => 'knowledge_post_id',
        GoalPost::class => 'goal_post_id',
        ProgressReport::class => 'progress_report_id',
        AchievementReport::class => 'achievement_report_id',
    ];

    /**
     * ランダムに選んだ1種類の投稿にだけ紐づく外部キーの配列を返す
     *
     * @return array<string, mixed>
     */
    protected function randomPostAttributes(): array
    {
        $postModel = fake()->randomElement(array_keys(self::POST_FOREIGN_KEYS));

        return [
            ...array_fill_keys(self::POST_FOREIGN_KEYS, null),
            self::POST_FOREIGN_KEYS[$postModel] => $postModel::factory(),
        ];
    }

    /**
     * 紐づけ先の投稿を指定する
     */
    public function forPost(KnowledgePost|GoalPost|ProgressReport|AchievementReport $post): static
    {
        return $this->state(fn (array $attributes) => [
            ...array_fill_keys(self::POST_FOREIGN_KEYS, null),
            self::POST_FOREIGN_KEYS[$post::class] => $post->id,
        ]);
    }
}
