<?php

namespace Database\Factories;

use App\Models\AchievementReport;
use App\Models\GoalPost;
use Database\Factories\Concerns\GeneratesPostContent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AchievementReport>
 */
class AchievementReportFactory extends Factory
{
    use GeneratesPostContent;

    private const TITLES = [
        '目標を達成しました！',
        'ついに完走できました',
        '合格しました！',
        '3か月続けられました',
        '最後までやり切りました',
    ];

    private const SENTENCES = [
        '応援してくださった皆さん、ありがとうございました。',
        '途中で何度もくじけそうになりましたが、続けてよかったです。',
        '次はもう一段上の目標に挑戦します。',
        '毎日の小さな積み重ねが大事だと実感しました。',
    ];

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
            'title' => fake()->randomElement(self::TITLES),
            'body' => $this->bodyFrom(self::SENTENCES),
            // 画像はビューで storage/ からの相対パスとして表示するので、テストデータでは付けない
            'image_path' => null,
            // 達成報告は目標を投稿したあとに書かれる
            'created_at' => fn (array $attributes) => $this->postedAfterGoal($attributes),
            'updated_at' => fn (array $attributes) => $attributes['created_at'],
        ];
    }
}
