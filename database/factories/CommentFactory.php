<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\User;
use Database\Factories\Concerns\BelongsToPost;
use Illuminate\Database\Eloquent\Factories\Factory;

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
            'body' => fake()->text(),
        ];
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
