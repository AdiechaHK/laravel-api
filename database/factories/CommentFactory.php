<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Comment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'body' => $this->faker->paragraph(),
        ];
    }

    /**
     * Indicate that the comment has a short body.
     */
    public function short(): static
    {
        return $this->state(fn (array $attributes) => [
            'body' => $this->faker->sentence(),
        ]);
    }

    /**
     * Indicate that the comment has a long body.
     */
    public function long(): static
    {
        return $this->state(fn (array $attributes) => [
            'body' => $this->faker->paragraphs(3, true),
        ]);
    }
} 