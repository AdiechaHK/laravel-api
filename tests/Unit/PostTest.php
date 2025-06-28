<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_can_have_comments(): void
    {
        $post = Post::factory()->create();
        $comments = Comment::factory()->count(3)->create(['post_id' => $post->id]);

        $this->assertCount(3, $post->comments);
        $this->assertTrue($post->comments->contains($comments->first()));
    }

    public function test_post_can_be_created_with_fillable_fields(): void
    {
        $postData = [
            'title' => 'Test Post Title',
            'body' => 'Test post body content',
        ];

        $post = Post::create($postData);

        $this->assertDatabaseHas('posts', $postData);
        $this->assertEquals($postData['title'], $post->title);
        $this->assertEquals($postData['body'], $post->body);
    }

    public function test_post_can_be_updated(): void
    {
        $post = Post::factory()->create();
        $updateData = [
            'title' => 'Updated Title',
            'body' => 'Updated body content',
        ];

        $post->update($updateData);

        $this->assertDatabaseHas('posts', $updateData);
        $this->assertEquals($updateData['title'], $post->title);
        $this->assertEquals($updateData['body'], $post->fresh()->body);
    }

    public function test_post_can_be_deleted(): void
    {
        $post = Post::factory()->create();
        $postId = $post->id;

        $post->delete();

        $this->assertDatabaseMissing('posts', ['id' => $postId]);
    }

    public function test_post_has_correct_table_name(): void
    {
        $post = new Post();
        $this->assertEquals('posts', $post->getTable());
    }

    public function test_post_has_correct_primary_key(): void
    {
        $post = new Post();
        $this->assertEquals('id', $post->getKeyName());
    }

    public function test_post_factory_creates_valid_data(): void
    {
        $post = Post::factory()->create();

        $this->assertNotEmpty($post->title);
        $this->assertNotEmpty($post->body);
        $this->assertIsString($post->title);
        $this->assertIsString($post->body);
    }

    public function test_post_factory_short_title_state(): void
    {
        $post = Post::factory()->shortTitle()->create();

        $this->assertLessThanOrEqual(50, strlen($post->title));
    }

    public function test_post_factory_long_body_state(): void
    {
        $post = Post::factory()->longBody()->create();

        $this->assertGreaterThan(200, strlen($post->body));
    }
} 