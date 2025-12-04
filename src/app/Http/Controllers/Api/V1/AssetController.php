<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AssetController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $assets = Asset::where('tenant_id', $request->user()->tenant_id)
            ->with(['category', 'currentAssignment.employee'])
            ->paginate($request->get('per_page', 15));

        return response()->json($assets);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:asset_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'serial_number' => 'nullable|string|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric',
            'location' => 'nullable|string|max:255',
            'status' => 'nullable|in:available,assigned,maintenance,retired',
            'condition' => 'nullable|in:new,good,fair,poor,damaged',
        ]);

        $validated['tenant_id'] = $request->user()->tenant_id;

        $asset = Asset::create($validated);

        return response()->json($asset->load('category'), 201);
    }

    public function show(Request $request, Asset $asset): JsonResponse
    {
        $this->authorize('view', $asset);

        return response()->json($asset->load(['category', 'assignments.employee', 'maintenances']));
    }

    public function update(Request $request, Asset $asset): JsonResponse
    {
        $this->authorize('update', $asset);

        $validated = $request->validate([
            'category_id' => 'sometimes|exists:asset_categories,id',
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'serial_number' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'status' => 'nullable|in:available,assigned,maintenance,retired',
            'condition' => 'nullable|in:new,good,fair,poor,damaged',
        ]);

        $asset->update($validated);

        return response()->json($asset->load('category'));
    }

    public function destroy(Request $request, Asset $asset): JsonResponse
    {
        $this->authorize('delete', $asset);

        $asset->delete();

        return response()->json(null, 204);
    }

    public function assign(Request $request, Asset $asset): JsonResponse
    {
        $this->authorize('assign', $asset);

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'notes' => 'nullable|string',
        ]);

        $employee = \App\Models\Employee::findOrFail($validated['employee_id']);
        $assignment = $asset->assignTo($employee, $request->user(), $validated['notes'] ?? null);

        return response()->json($assignment->load(['asset', 'employee']));
    }
}
