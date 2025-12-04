<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Timesheet;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TimesheetController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $timesheets = Timesheet::where('tenant_id', $request->user()->tenant_id)
            ->with('employee')
            ->paginate($request->get('per_page', 15));

        return response()->json($timesheets);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'clock_in' => 'required|date_format:H:i',
            'clock_out' => 'nullable|date_format:H:i|after:clock_in',
            'notes' => 'nullable|string',
        ]);

        $validated['tenant_id'] = $request->user()->tenant_id;

        $timesheet = Timesheet::create($validated);

        return response()->json($timesheet->load('employee'), 201);
    }

    public function show(Request $request, Timesheet $timesheet): JsonResponse
    {
        $this->authorize('view', $timesheet);

        return response()->json($timesheet->load('employee'));
    }

    public function update(Request $request, Timesheet $timesheet): JsonResponse
    {
        $this->authorize('update', $timesheet);

        $validated = $request->validate([
            'clock_in' => 'sometimes|date_format:H:i',
            'clock_out' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string',
        ]);

        $timesheet->update($validated);

        return response()->json($timesheet->load('employee'));
    }

    public function destroy(Request $request, Timesheet $timesheet): JsonResponse
    {
        $this->authorize('delete', $timesheet);

        $timesheet->delete();

        return response()->json(null, 204);
    }

    public function approve(Request $request, Timesheet $timesheet): JsonResponse
    {
        $this->authorize('approve', $timesheet);

        $timesheet->update(['status' => 'approved']);

        return response()->json($timesheet);
    }

    public function reject(Request $request, Timesheet $timesheet): JsonResponse
    {
        $this->authorize('reject', $timesheet);

        $timesheet->update(['status' => 'rejected']);

        return response()->json($timesheet);
    }
}
