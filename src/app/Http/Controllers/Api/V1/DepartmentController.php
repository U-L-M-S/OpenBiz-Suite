<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DepartmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $departments = Department::where('tenant_id', $request->user()->tenant_id)
            ->with('manager')
            ->paginate($request->get('per_page', 15));

        return response()->json($departments);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'manager_id' => 'nullable|exists:employees,id',
            'is_active' => 'boolean',
        ]);

        $validated['tenant_id'] = $request->user()->tenant_id;

        $department = Department::create($validated);

        return response()->json($department->load('manager'), 201);
    }

    public function show(Request $request, Department $department): JsonResponse
    {
        $this->authorize('view', $department);

        return response()->json($department->load(['manager', 'employees']));
    }

    public function update(Request $request, Department $department): JsonResponse
    {
        $this->authorize('update', $department);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'manager_id' => 'nullable|exists:employees,id',
            'is_active' => 'boolean',
        ]);

        $department->update($validated);

        return response()->json($department->load('manager'));
    }

    public function destroy(Request $request, Department $department): JsonResponse
    {
        $this->authorize('delete', $department);

        $department->delete();

        return response()->json(null, 204);
    }
}
