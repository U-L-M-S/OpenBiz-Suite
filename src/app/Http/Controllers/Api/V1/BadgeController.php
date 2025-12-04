<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\UserPoints;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BadgeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $badges = Badge::where('tenant_id', $request->user()->tenant_id)
            ->where('is_active', true)
            ->withCount('users')
            ->paginate($request->get('per_page', 15));

        return response()->json($badges);
    }

    public function show(Request $request, Badge $badge): JsonResponse
    {
        $this->authorize('view', $badge);

        $earned = $badge->users()->where('user_id', $request->user()->id)->exists();

        return response()->json([
            'badge' => $badge,
            'earned' => $earned,
        ]);
    }

    public function myBadges(Request $request): JsonResponse
    {
        $badges = $request->user()
            ->badges()
            ->withPivot('earned_at', 'metadata')
            ->get();

        return response()->json($badges);
    }

    public function myPoints(Request $request): JsonResponse
    {
        $totalPoints = UserPoints::getTotalPoints($request->user());

        $history = UserPoints::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'total_points' => $totalPoints,
            'history' => $history,
        ]);
    }

    public function leaderboard(Request $request): JsonResponse
    {
        $leaderboard = UserPoints::where('tenant_id', $request->user()->tenant_id)
            ->selectRaw('user_id, SUM(points) as total_points')
            ->groupBy('user_id')
            ->orderByDesc('total_points')
            ->limit(20)
            ->with('user:id,name')
            ->get();

        return response()->json($leaderboard);
    }
}
