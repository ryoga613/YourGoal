<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\User;
use Carbon\CarbonInterface;
use Database\Factories\Concerns\BelongsToPost;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\Factory;
use LogicException;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    use BelongsToPost;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            ...$this->randomPostAttributes(),
            'parent_id' => null,
            'body' => fake('ja_JP')->realText(),
            // コメントは投稿（返信なら親コメント）のあとに書かれる
            'created_at' => fn (array $attributes) => $this->commentedAt($attributes),
            'updated_at' => fn (array $attributes) => $attributes['created_at'],
        ];
    }

    /**
     * コメントの投稿日時（コメント先の投稿、返信なら親コメントの投稿日時から今までの間でばらけさせる）
     *
     * シーダーは投稿の種類ごとに順番にコメントを作るため、作った瞬間の日時のままだと
     * 新しい順に並べたときに、最後に作った種類のコメントばかりが上に来てしまう
     *
     * @param  array<string, mixed>  $attributes
     */
    private function commentedAt(array $attributes): DateTimeInterface
    {
        $after = $attributes['parent_id']
            ? Comment::findOrFail($attributes['parent_id'])->created_at
            : $this->postCreatedAt($attributes);

        // テストで未来の日時の投稿を作ったときは、その日時にする（範囲の始まりが今より後だと選べないため）
        return $after->isFuture() ? $after : fake()->dateTimeBetween($after, 'now');
    }

    /**
     * 紐づけ先の投稿の投稿日時
     *
     * @param  array<string, mixed>  $attributes
     */
    private function postCreatedAt(array $attributes): CarbonInterface
    {
        foreach (self::POST_FOREIGN_KEYS as $postModel => $foreignKey) {
            if ($attributes[$foreignKey]) {
                return $postModel::findOrFail($attributes[$foreignKey])->created_at;
            }
        }

        throw new LogicException('コメントの紐づけ先の投稿がありません。');
    }

    /**
     * 指定したコメントへの返信にする（紐づけ先の投稿は親コメントと同じにする）
     */
    public function replyTo(Comment $parent): static
    {
        return $this->state(fn (array $attributes) => [
            ...$parent->only(self::POST_FOREIGN_KEYS),
            'parent_id' => $parent->id,
        ]);
    }
}
