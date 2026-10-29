<?php

namespace Database\Factories;

use App\Models\CommentLike;
use App\Models\User;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommentLike>
 */
class CommentLikeFactory extends Factory
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
            'comment_id' =>Comment::factory(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
