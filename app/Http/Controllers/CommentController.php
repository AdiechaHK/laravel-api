<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Comment;
use App\Models\Post;
use App\Http\Resources\CommentResource;
use App\Http\Resources\CommentCollection;
use App\Http\Requests\StoreCommentRequest;

class CommentController extends Controller
{
    public function index(int $postId): CommentCollection
    {
        if (!Post::find($postId)) {
            abort(404, 'Post not found');
        }
        return CommentCollection::make(Comment::where('post_id', $postId)->get());
    }

    public function store(StoreCommentRequest $request, int $postId): CommentResource
    {
        $post = Post::find($postId);
        if (!$post) {
            abort(404, 'Post not found');
        }
        $comment = Comment::create(array_merge($request->validated(), ['post_id' => $postId]));
        return CommentResource::make($comment);
    }

    public function show(Comment $comment): CommentResource
    {
        $this->authorize('view', $comment);
        return CommentResource::make($comment);
    }

    public function update(StoreCommentRequest $request, Comment $comment): CommentResource
    {
        $this->authorize('update', $comment);
        $comment->update($request->validated());
        return CommentResource::make($comment);
    }

    public function destroy(Comment $comment): Response
    {
        $this->authorize('delete', $comment);
        $comment->delete();
        return response()->noContent();
    }
}