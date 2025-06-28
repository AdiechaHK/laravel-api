<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Comment;
use App\Http\Resources\CommentResource;
use App\Http\Resources\CommentCollection;
use App\Http\Requests\StoreCommentRequest;

class CommentController extends Controller
{
    public function index(int $postId): CommentCollection
    {
        return CommentCollection::make(Comment::where('post_id', $postId)->get());
    }

    public function store(StoreCommentRequest $request, int $postId): CommentResource
    {
        $comment = Comment::create(array_merge($request->validated(), ['post_id' => $postId]));
        return CommentResource::make($comment);
    }

    public function show(int $postId, int $id): CommentResource
    {
        $comment = Comment::where('post_id', $postId)->findOrFail($id);
        $this->authorize('view', $comment);
        return CommentResource::make($comment);
    }

    public function update(StoreCommentRequest $request, int $postId, int $id): CommentResource
    {
        $comment = Comment::where('post_id', $postId)->findOrFail($id);
        $this->authorize('update', $comment);
        $comment->update($request->validated());
        return CommentResource::make($comment);
    }

    public function destroy(int $postId, int $id): Response
    {
        $comment = Comment::where('post_id', $postId)->findOrFail($id);
        $this->authorize('delete', $comment);
        $comment->delete();
        return response()->noContent();
    }
}