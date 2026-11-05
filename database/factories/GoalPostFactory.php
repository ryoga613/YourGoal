<?php

namespace Database\Factories;

use App\Models\GoalPost;
use App\Models\User;
use Database\Factories\Concerns\GeneratesPostContent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GoalPost>
 */
class GoalPostFactory extends Factory
{
    use GeneratesPostContent;

    private const TITLES = [
        '毎日30分、英語の勉強を続ける',
        '3か月で体重を5kg落とす',
        '毎朝6時に起きる習慣をつける',
        '月に2冊本を読む',
        '基本情報技術者試験に合格する',
        '毎日1万歩歩く',
        'ポートフォリオサイトを完成させる',
        'フルマラソンを完走する',
        '毎日日記を書く',
        '簿記2級を取る',
        '自炊を週5日続ける',
        'ギターで1曲弾けるようになる',
    ];

    private const SENTENCES = [
        'まずは小さな目標から始めて、少しずつ習慣にしていきます。',
        '続けられるように、毎日の予定に組み込みました。',
        '三日坊主にならないよう、ここで宣言します！',
        '達成できたら自分にご褒美をあげる予定です。',
        '同じ目標の人がいたら一緒に頑張りましょう。',
        '忙しい日でも5分だけはやると決めています。',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->randomElement(self::TITLES),
            'body' => $this->bodyFrom(self::SENTENCES),
            'status' => fake()->randomElement(['considering', 'in_progress', 'achieved', 'cancelled']),
            // 画像はビューで storage/ からの相対パスとして表示するので、テストデータでは付けない
            'image_path' => null,
            'created_at' => $this->postedAt(),
            'updated_at' => fn (array $attributes) => $attributes['created_at'],
        ];
    }
}
