<?php

namespace Database\Factories;

use App\Models\GoalPost;
use App\Models\ProgressReport;
use Database\Factories\Concerns\GeneratesPostContent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProgressReport>
 */
class ProgressReportFactory extends Factory
{
    use GeneratesPostContent;

    private const TITLES = [
        '1週間続けられました',
        '今日で10日目',
        '少しペースが落ちてきた',
        '先週の振り返り',
        '目標の半分まで来ました',
        'つまずいたけど再開しました',
        '記録を見返してみた',
        '新しいやり方を試してみた',
    ];

    private const SENTENCES = [
        '最初は大変でしたが、だんだん慣れてきました。',
        '思ったより時間がかかっていますが、焦らず続けます。',
        '記録をつけるようにしたら、モチベーションが上がりました。',
        '昨日はできなかったので、今日は少し多めに取り組みました。',
        '朝のうちに済ませると、1日がスムーズに進みます。',
        '次の1週間は、量より質を意識してみます。',
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
            // 進捗報告は目標を投稿したあとに書かれる
            'created_at' => fn (array $attributes) => $this->postedAfterGoal($attributes),
            'updated_at' => fn (array $attributes) => $attributes['created_at'],
        ];
    }
}
