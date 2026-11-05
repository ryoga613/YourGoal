<?php

namespace Database\Factories;

use App\Models\KnowledgePost;
use App\Models\User;
use Database\Factories\Concerns\GeneratesPostContent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KnowledgePost>
 */
class KnowledgePostFactory extends Factory
{
    use GeneratesPostContent;

    private const TITLES = [
        '習慣化のコツは「ハードルを下げる」こと',
        '勉強時間を確保するための朝活のすすめ',
        'やる気が出ない日の乗り切り方',
        '記録アプリで進捗を見える化する方法',
        'ポモドーロ・テクニックを試してみた',
        '人に説明すると理解が深まる',
        '目標は数字で決めると続けやすい',
    ];

    private const SENTENCES = [
        '最初から完璧を目指さず、毎日少しでも続けることを優先しています。',
        '25分集中して5分休むサイクルが、自分には合っていました。',
        'できた日にカレンダーへ印をつけるだけでも、続ける力になります。',
        'インプットだけで終わらせず、自分の言葉でまとめるようにしています。',
        '疲れている日は「1分だけやる」と決めると、意外と続けられます。',
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
            // 画像はビューで storage/ からの相対パスとして表示するので、テストデータでは付けない
            'image_path' => null,
            'created_at' => $this->postedAt(),
            'updated_at' => fn (array $attributes) => $attributes['created_at'],
        ];
    }
}
