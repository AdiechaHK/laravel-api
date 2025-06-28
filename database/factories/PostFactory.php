<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Post::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'body' => $this->faker->paragraphs(3, true),
        ];
    }

    /**
     * Indicate that the post has a short title.
     */
    public function shortTitle(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => $this->faker->words(2, true),
        ]);
    }

    /**
     * Indicate that the post has a long body.
     */
    public function longBody(): static
    {
        return $this->state(fn (array $attributes) => [
            'body' => $this->faker->paragraphs(10, true),
        ]);
    }
} 