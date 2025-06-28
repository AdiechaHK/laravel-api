<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class PostTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a user and get authentication token
        $this->user = User::factory()->create();
        $response = $this->postJson('/api/login', [
            'email' => $this->user->email,
            'password' => 'password',
        ]);
        
        $this->token = $response->json('access_token');
    }

    public function test_user_can_get_all_posts(): void
    {
        // Create some posts
        Post::factory()->count(3)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/posts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'body',
                        'created_at',
                        'updated_at',
                        'comments'
                    ]
                ]
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    public function test_user_can_create_post(): void
    {
        $postData = [
            'title' => $this->faker->sentence,
            'body' => $this->faker->paragraph,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/posts', $postData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'body',
                    'created_at',
                    'updated_at'
                ]
            ])
            ->assertJson([
                'data' => [
                    'title' => $postData['title'],
                    'body' => $postData['body'],
                ]
            ]);

        $this->assertDatabaseHas('posts', $postData);
    }

    public function test_user_can_view_single_post(): void
    {
        $post = Post::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/posts/{$post->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'body',
                    'created_at',
                    'updated_at',
                    'comments'
                ]
            ])
            ->assertJson([
                'data' => [
                    'id' => $post->id,
                    'title' => $post->title,
                    'body' => $post->body,
                ]
            ]);
    }

    public function test_user_can_update_post(): void
    {
        $post = Post::factory()->create();
        $updateData = [
            'title' => 'Updated Title',
            'body' => 'Updated body content',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/posts/{$post->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $post->id,
                    'title' => $updateData['title'],
                    'body' => $updateData['body'],
                ]
            ]);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => $updateData['title'],
            'body' => $updateData['body'],
        ]);
    }

    public function test_user_can_delete_post(): void
    {
        $post = Post::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/posts/{$post->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_post_validation_requires_title(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/posts', [
            'body' => $this->faker->paragraph,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_post_validation_requires_body(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/posts', [
            'title' => $this->faker->sentence,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['body']);
    }

    public function test_post_validation_title_max_length(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/posts', [
            'title' => str_repeat('a', 256), // Exceeds 255 character limit
            'body' => $this->faker->paragraph,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_post_validation_title_must_be_string(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/posts', [
            'title' => 123,
            'body' => $this->faker->paragraph,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_post_validation_body_must_be_string(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/posts', [
            'title' => $this->faker->sentence,
            'body' => 123,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['body']);
    }

    public function test_returns_404_for_nonexistent_post(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/posts/99999');

        $response->assertStatus(404);
    }

    public function test_returns_404_when_updating_nonexistent_post(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson('/api/posts/99999', [
            'title' => 'Updated Title',
            'body' => 'Updated body',
        ]);

        $response->assertStatus(404);
    }

    public function test_returns_404_when_deleting_nonexistent_post(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson('/api/posts/99999');

        $response->assertStatus(404);
    }

    public function test_post_includes_comments_when_viewed(): void
    {
        $post = Post::factory()->create();
        $comments = Comment::factory()->count(3)->create(['post_id' => $post->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/posts/{$post->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'body',
                    'created_at',
                    'updated_at',
                    'comments' => [
                        '*' => [
                            'id',
                            'body',
                            'post_id',
                            'created_at',
                            'updated_at'
                        ]
                    ]
                ]
            ]);

        $this->assertCount(3, $response->json('data.comments'));
    }
} 