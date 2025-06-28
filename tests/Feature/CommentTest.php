<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class CommentTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private Post $post;
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
        
        // Create a post for comment tests
        $this->post = Post::factory()->create();
    }

    public function test_user_can_get_all_comments_for_post(): void
    {
        // Create some comments for the post
        Comment::factory()->count(3)->create(['post_id' => $this->post->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/posts/{$this->post->id}/comments");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'body',
                        'post_id',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    public function test_user_can_create_comment(): void
    {
        $commentData = [
            'body' => $this->faker->paragraph,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/posts/{$this->post->id}/comments", $commentData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'body',
                    'post_id',
                    'created_at',
                    'updated_at'
                ]
            ])
            ->assertJson([
                'data' => [
                    'body' => $commentData['body'],
                    'post_id' => $this->post->id,
                ]
            ]);

        $this->assertDatabaseHas('comments', [
            'body' => $commentData['body'],
            'post_id' => $this->post->id,
        ]);
    }

    public function test_user_can_view_single_comment(): void
    {
        $comment = Comment::factory()->create(['post_id' => $this->post->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/comments/{$comment->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'body',
                    'post_id',
                    'created_at',
                    'updated_at'
                ]
            ])
            ->assertJson([
                'data' => [
                    'id' => $comment->id,
                    'body' => $comment->body,
                    'post_id' => $this->post->id,
                ]
            ]);
    }

    public function test_user_can_update_comment(): void
    {
        $comment = Comment::factory()->create(['post_id' => $this->post->id]);
        $updateData = [
            'body' => 'Updated comment content',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/comments/{$comment->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $comment->id,
                    'body' => $updateData['body'],
                    'post_id' => $this->post->id,
                ]
            ]);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'body' => $updateData['body'],
            'post_id' => $this->post->id,
        ]);
    }

    public function test_user_can_delete_comment(): void
    {
        $comment = Comment::factory()->create(['post_id' => $this->post->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_comment_validation_requires_body(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/posts/{$this->post->id}/comments", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['body']);
    }

    public function test_comment_validation_body_must_be_string(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/posts/{$this->post->id}/comments", [
            'body' => 123,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['body']);
    }

    public function test_returns_404_for_nonexistent_comment(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/comments/99999");

        $response->assertStatus(404);
    }

    public function test_returns_404_when_updating_nonexistent_comment(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/comments/99999", [
            'body' => 'Updated comment',
        ]);

        $response->assertStatus(404);
    }

    public function test_returns_404_when_deleting_nonexistent_comment(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/comments/99999");

        $response->assertStatus(404);
    }

    public function test_returns_404_for_comment_on_nonexistent_post(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/posts/99999/comments');

        $response->assertStatus(404);
    }

    public function test_comment_belongs_to_correct_post(): void
    {
        $otherPost = Post::factory()->create();
        $comment = Comment::factory()->create(['post_id' => $this->post->id]);

        // Try to access comment through wrong post
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/posts/{$otherPost->id}/comments/{$comment->id}");

        $response->assertStatus(404);
    }

    public function test_cannot_update_comment_through_wrong_post(): void
    {
        $otherPost = Post::factory()->create();
        $comment = Comment::factory()->create(['post_id' => $this->post->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/posts/{$otherPost->id}/comments/{$comment->id}", [
            'body' => 'Updated comment',
        ]);

        $response->assertStatus(404);
    }

    public function test_cannot_delete_comment_through_wrong_post(): void
    {
        $otherPost = Post::factory()->create();
        $comment = Comment::factory()->create(['post_id' => $this->post->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/posts/{$otherPost->id}/comments/{$comment->id}");

        $response->assertStatus(404);
    }

    public function test_comments_are_scoped_to_specific_post(): void
    {
        $otherPost = Post::factory()->create();
        
        // Create comments for both posts
        Comment::factory()->count(2)->create(['post_id' => $this->post->id]);
        Comment::factory()->count(3)->create(['post_id' => $otherPost->id]);

        // Get comments for first post
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/posts/{$this->post->id}/comments");

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));

        // Get comments for second post
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/posts/{$otherPost->id}/comments");

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }
} 