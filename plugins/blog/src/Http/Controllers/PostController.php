<?php

namespace YellowThree\VoyagerBlog\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use YellowThree\VoyagerBlog\Models\Post;

class PostController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Post::with('category')->latest()->paginate(10));
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(Post::with('category')->findOrFail($id));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'slug' => 'required|unique:posts,slug',
            'body' => 'nullable',
            'excerpt' => 'nullable',
            'status' => 'nullable|in:PUBLISHED,DRAFT,PENDING',
            'category_id' => 'nullable|exists:categories,id',
            'featured' => 'nullable|boolean',
        ]);

        $post = Post::create($validated + ['author_id' => auth()->id()]);

        return response()->json($post, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $post = Post::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|max:255',
            'slug' => 'required|unique:posts,slug,' . $id,
            'body' => 'nullable',
            'excerpt' => 'nullable',
            'status' => 'nullable|in:PUBLISHED,DRAFT,PENDING',
            'category_id' => 'nullable|exists:categories,id',
            'featured' => 'nullable|boolean',
        ]);

        $post->update($validated);

        return response()->json($post);
    }

    public function destroy(int $id): JsonResponse
    {
        Post::findOrFail($id)->delete();

        return response()->json(['message' => __('voyager::generic.successfully_deleted')]);
    }
}
