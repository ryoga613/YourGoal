<?php

namespace Database\Factories;

use App\Models\PostLike;
use App\Models\User;
use Database\Factories\Concerns\BelongsToPost;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PostLike>
 */
class PostLikeFactory extends Factory
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
        ];
    }
}
