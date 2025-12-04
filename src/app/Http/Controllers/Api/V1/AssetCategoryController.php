<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AssetCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AssetCategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $categories = AssetCategory::where('tenant_id', $request->user()->tenant_id)
            ->withCount('assets')
            ->paginate($request->get('per_page', 15));

        return response()->json($categories);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['tenant_id'] = $request->user()->tenant_id;

        $category = AssetCategory::create($validated);

        return response()->json($category, 201);
    }

    public function show(Request $request, AssetCategory $assetCategory): JsonResponse
    {
        $this->authorize('view', $assetCategory);

        return response()->json($assetCategory->loadCount('assets'));
    }

    public function update(Request $request, AssetCategory $assetCategory): JsonResponse
    {
        $this->authorize('update', $assetCategory);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $assetCategory->update($validated);

        return response()->json($assetCategory);
    }

    public function destroy(Request $request, AssetCategory $assetCategory): JsonResponse
    {
        $this->authorize('delete', $assetCategory);

        $assetCategory->delete();

        return response()->json(null, 204);
    }
}
