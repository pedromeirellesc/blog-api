<?php

namespace Database\Factories;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            'post_id' => \App\Models\Post::factory(),
            'parent_id' => $this->faker->boolean(30) ? Comment::inRandomOrder()->first() : null,
            'user_id' => \App\Models\User::factory(),
            // Setar novos campos
            'content' => $this->faker->paragraph,
            'created_at' => $this->faker->dateTimeBetween('-1 year'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year'),
        ];
    }
}
