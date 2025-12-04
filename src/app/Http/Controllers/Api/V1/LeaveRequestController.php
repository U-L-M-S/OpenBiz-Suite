<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LeaveRequestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $leaveRequests = LeaveRequest::where('tenant_id', $request->user()->tenant_id)
            ->with(['employee', 'leaveType'])
            ->paginate($request->get('per_page', 15));

        return response()->json($leaveRequests);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
        ]);

        $validated['tenant_id'] = $request->user()->tenant_id;
        $validated['status'] = 'pending';

        $leaveRequest = LeaveRequest::create($validated);

        return response()->json($leaveRequest->load(['employee', 'leaveType']), 201);
    }

    public function show(Request $request, LeaveRequest $leaveRequest): JsonResponse
    {
        $this->authorize('view', $leaveRequest);

        return response()->json($leaveRequest->load(['employee', 'leaveType', 'approvedBy']));
    }

    public function update(Request $request, LeaveRequest $leaveRequest): JsonResponse
    {
        $this->authorize('update', $leaveRequest);

        $validated = $request->validate([
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
        ]);

        $leaveRequest->update($validated);

        return response()->json($leaveRequest->load(['employee', 'leaveType']));
    }

    public function destroy(Request $request, LeaveRequest $leaveRequest): JsonResponse
    {
        $this->authorize('delete', $leaveRequest);

        $leaveRequest->delete();

        return response()->json(null, 204);
    }

    public function approve(Request $request, LeaveRequest $leaveRequest): JsonResponse
    {
        $this->authorize('approve', $leaveRequest);

        $leaveRequest->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return response()->json($leaveRequest->load(['employee', 'leaveType']));
    }

    public function reject(Request $request, LeaveRequest $leaveRequest): JsonResponse
    {
        $this->authorize('reject', $leaveRequest);

        $validated = $request->validate([
            'rejection_reason' => 'nullable|string',
        ]);

        $leaveRequest->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'] ?? null,
        ]);

        return response()->json($leaveRequest->load(['employee', 'leaveType']));
    }
}
