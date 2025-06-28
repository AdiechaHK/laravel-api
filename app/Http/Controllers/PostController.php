<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Post;
use App\Http\Resources\PostResource;
use App\Http\Resources\PostCollection;
use App\Http\Requests\StorePostRequest;

class PostController extends Controller
{
    public function index(): PostCollection
    {
            return PostCollection::make(Post::with('comments')->get());
    }

    public function store(StorePostRequest $request): PostResource
    {
        $post = Post::create($request->validated());
        return PostResource::make($post);
    }

    public function show(int $id): PostResource
    {
        $post = Post::with('comments')->findOrFail($id);
        $this->authorize('view', $post);
        return PostResource::make($post);
    }

    public function update(StorePostRequest $request, int $id): PostResource
    {
        $post = Post::findOrFail($id);
        $this->authorize('update', $post);
        $post->update($request->validated());
        return PostResource::make($post);
    }

    public function destroy(int $id): Response
    {
        $post = Post::findOrFail($id);
        $this->authorize('delete', $post);
        $post->delete();
        return response()->noContent();
    }
}