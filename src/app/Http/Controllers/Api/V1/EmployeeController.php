<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EmployeeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $employees = Employee::where('tenant_id', $request->user()->tenant_id)
            ->with(['department', 'position'])
            ->paginate($request->get('per_page', 15));

        return response()->json($employees);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email',
            'phone' => 'nullable|string|max:50',
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'hire_date' => 'nullable|date',
            'status' => 'nullable|in:active,inactive,terminated',
        ]);

        $validated['tenant_id'] = $request->user()->tenant_id;

        $employee = Employee::create($validated);

        return response()->json($employee->load(['department', 'position']), 201);
    }

    public function show(Request $request, Employee $employee): JsonResponse
    {
        $this->authorize('view', $employee);

        return response()->json($employee->load(['department', 'position', 'manager']));
    }

    public function update(Request $request, Employee $employee): JsonResponse
    {
        $this->authorize('update', $employee);

        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:employees,email,' . $employee->id,
            'phone' => 'nullable|string|max:50',
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'hire_date' => 'nullable|date',
            'status' => 'nullable|in:active,inactive,terminated',
        ]);

        $employee->update($validated);

        return response()->json($employee->load(['department', 'position']));
    }

    public function destroy(Request $request, Employee $employee): JsonResponse
    {
        $this->authorize('delete', $employee);

        $employee->delete();

        return response()->json(null, 204);
    }
}
