<?php

namespace YellowThree\VoyagerMenu\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use YellowThree\VoyagerMenu\Models\Menu;

class MenuController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Menu::withCount('items')->orderBy('name')->paginate(10));
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(Menu::with('items')->findOrFail($id));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|max:255|unique:menus,name',
        ]);

        $menu = Menu::create($validated);

        return response()->json($menu, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $menu = Menu::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|max:255|unique:menus,name,' . $id,
        ]);

        $menu->update($validated);

        return response()->json($menu);
    }

    public function destroy(int $id): JsonResponse
    {
        Menu::findOrFail($id)->delete();

        return response()->json(['message' => __('voyager::generic.successfully_deleted')]);
    }
}
