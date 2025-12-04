<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AssetAssignment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AssetAssignmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $assignments = AssetAssignment::where('tenant_id', $request->user()->tenant_id)
            ->with(['asset', 'employee', 'assignedBy'])
            ->paginate($request->get('per_page', 15));

        return response()->json($assignments);
    }

    public function show(Request $request, AssetAssignment $assetAssignment): JsonResponse
    {
        $this->authorize('view', $assetAssignment);

        return response()->json($assetAssignment->load(['asset', 'employee', 'assignedBy']));
    }

    public function returnAsset(Request $request, AssetAssignment $assetAssignment): JsonResponse
    {
        $this->authorize('update', $assetAssignment);

        $validated = $request->validate([
            'notes' => 'nullable|string',
            'status' => 'nullable|in:returned,damaged,lost',
        ]);

        $assetAssignment->returnAsset(
            $validated['notes'] ?? null,
            $validated['status'] ?? 'returned'
        );

        return response()->json($assetAssignment->fresh()->load(['asset', 'employee']));
    }
}
