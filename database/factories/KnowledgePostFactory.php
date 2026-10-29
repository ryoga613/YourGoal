<?php

namespace Database\Factories;

use App\Models\KnowledgePost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KnowledgePost>
 */
class KnowledgePostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(),
            'body' => fake()->text(),
            'image_path' => 'https://picsum.photos/seed/'.fake()->uuid().'/640/480',
        ];
    }
}
