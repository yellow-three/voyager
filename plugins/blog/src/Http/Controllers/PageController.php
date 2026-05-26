<?php

namespace YellowThree\VoyagerBlog\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use YellowThree\VoyagerBlog\Models\Page;

class PageController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Page::latest()->paginate(10));
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(Page::findOrFail($id));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'slug' => 'required|unique:pages,slug',
            'body' => 'nullable',
            'excerpt' => 'nullable',
            'status' => 'nullable|in:PUBLISHED,DRAFT',
        ]);

        $page = Page::create($validated + ['author_id' => auth()->id()]);

        return response()->json($page, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $page = Page::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|max:255',
            'slug' => 'required|unique:pages,slug,' . $id,
            'body' => 'nullable',
            'excerpt' => 'nullable',
            'status' => 'nullable|in:PUBLISHED,DRAFT',
        ]);

        $page->update($validated);

        return response()->json($page);
    }

    public function destroy(int $id): JsonResponse
    {
        Page::findOrFail($id)->delete();

        return response()->json(['message' => __('voyager::generic.successfully_deleted')]);
    }
}
