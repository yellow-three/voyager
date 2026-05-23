<?php

namespace YellowThree\VoyagerBlog\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use YellowThree\VoyagerBlog\Models\Category;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Category::withCount('posts')->orderBy('order')->paginate(10));
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(Category::withCount('posts')->findOrFail($id));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'slug' => 'required|unique:categories,slug',
            'parent_id' => 'nullable|exists:categories,id',
            'order' => 'nullable|integer',
        ]);

        $category = Category::create($validated);

        return response()->json($category, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|max:255',
            'slug' => 'required|unique:categories,slug,' . $id,
            'parent_id' => 'nullable|exists:categories,id',
            'order' => 'nullable|integer',
        ]);

        $category->update($validated);

        return response()->json($category);
    }

    public function destroy(int $id): JsonResponse
    {
        Category::findOrFail($id)->delete();

        return response()->json(['message' => __('voyager::generic.successfully_deleted')]);
    }
}
