<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_comment_belongs_to_post(): void
    {
        $post = Post::factory()->create();
        $comment = Comment::factory()->create(['post_id' => $post->id]);

        $this->assertInstanceOf(Post::class, $comment->post);
        $this->assertEquals($post->id, $comment->post->id);
    }

    public function test_comment_can_be_created_with_fillable_fields(): void
    {
        $post = Post::factory()->create();
        $commentData = [
            'post_id' => $post->id,
            'body' => 'Test comment body',
        ];

        $comment = Comment::create($commentData);

        $this->assertDatabaseHas('comments', $commentData);
        $this->assertEquals($commentData['body'], $comment->body);
        $this->assertEquals($commentData['post_id'], $comment->post_id);
    }

    public function test_comment_can_be_updated(): void
    {
        $comment = Comment::factory()->create();
        $updateData = [
            'body' => 'Updated comment body',
        ];

        $comment->update($updateData);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'body' => $updateData['body'],
        ]);
        $this->assertEquals($updateData['body'], $comment->fresh()->body);
    }

    public function test_comment_can_be_deleted(): void
    {
        $comment = Comment::factory()->create();
        $commentId = $comment->id;

        $comment->delete();

        $this->assertDatabaseMissing('comments', ['id' => $commentId]);
    }

    public function test_comment_has_correct_table_name(): void
    {
        $comment = new Comment();
        $this->assertEquals('comments', $comment->getTable());
    }

    public function test_comment_has_correct_primary_key(): void
    {
        $comment = new Comment();
        $this->assertEquals('id', $comment->getKeyName());
    }

    public function test_comment_factory_creates_valid_data(): void
    {
        $comment = Comment::factory()->create();

        $this->assertNotEmpty($comment->body);
        $this->assertIsString($comment->body);
        $this->assertNotNull($comment->post_id);
        $this->assertInstanceOf(Post::class, $comment->post);
    }

    public function test_comment_factory_short_state(): void
    {
        $comment = Comment::factory()->short()->create();

        $this->assertLessThanOrEqual(100, strlen($comment->body));
    }

    public function test_comment_factory_long_state(): void
    {
        $comment = Comment::factory()->long()->create();

        $this->assertGreaterThan(200, strlen($comment->body));
    }

    public function test_comment_can_access_post_relationship(): void
    {
        $post = Post::factory()->create();
        $comment = Comment::factory()->create(['post_id' => $post->id]);

        $this->assertInstanceOf(Post::class, $comment->post);
        $this->assertEquals($post->id, $comment->post->id);
        $this->assertEquals($post->title, $comment->post->title);
    }

    public function test_comment_factory_creates_post_if_not_provided(): void
    {
        $comment = Comment::factory()->create();

        $this->assertNotNull($comment->post_id);
        $this->assertInstanceOf(Post::class, $comment->post);
    }
} 