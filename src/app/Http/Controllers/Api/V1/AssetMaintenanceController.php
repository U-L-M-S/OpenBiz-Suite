<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AssetMaintenance;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AssetMaintenanceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $maintenances = AssetMaintenance::where('tenant_id', $request->user()->tenant_id)
            ->with(['asset', 'reportedBy'])
            ->paginate($request->get('per_page', 15));

        return response()->json($maintenances);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'type' => 'required|in:preventive,corrective,inspection,repair,upgrade',
            'description' => 'required|string',
            'scheduled_date' => 'required|date',
            'vendor' => 'nullable|string|max:255',
            'cost' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        $validated['tenant_id'] = $request->user()->tenant_id;
        $validated['reported_by'] = $request->user()->id;
        $validated['status'] = 'scheduled';

        $maintenance = AssetMaintenance::create($validated);

        return response()->json($maintenance->load(['asset', 'reportedBy']), 201);
    }

    public function show(Request $request, AssetMaintenance $assetMaintenance): JsonResponse
    {
        $this->authorize('view', $assetMaintenance);

        return response()->json($assetMaintenance->load(['asset', 'reportedBy']));
    }

    public function update(Request $request, AssetMaintenance $assetMaintenance): JsonResponse
    {
        $this->authorize('update', $assetMaintenance);

        $validated = $request->validate([
            'type' => 'sometimes|in:preventive,corrective,inspection,repair,upgrade',
            'description' => 'sometimes|string',
            'scheduled_date' => 'sometimes|date',
            'vendor' => 'nullable|string|max:255',
            'cost' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        $assetMaintenance->update($validated);

        return response()->json($assetMaintenance->load(['asset', 'reportedBy']));
    }

    public function destroy(Request $request, AssetMaintenance $assetMaintenance): JsonResponse
    {
        $this->authorize('delete', $assetMaintenance);

        $assetMaintenance->delete();

        return response()->json(null, 204);
    }

    public function complete(Request $request, AssetMaintenance $assetMaintenance): JsonResponse
    {
        $this->authorize('update', $assetMaintenance);

        $validated = $request->validate([
            'notes' => 'nullable|string',
            'cost' => 'nullable|numeric',
        ]);

        $assetMaintenance->complete(
            $validated['notes'] ?? null,
            $validated['cost'] ?? null
        );

        return response()->json($assetMaintenance->fresh()->load(['asset', 'reportedBy']));
    }
}
