<?php

namespace YellowThree\Voyager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of activity logs via API.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = DB::table('activity_logs')
                ->orderBy('created_at', 'DESC');

            if ($request->has('action')) {
                $query->where('action', $request->input('action'));
            }

            if ($request->has('model')) {
                $query->where('model_type', $request->input('model'));
            }

            $logs = $query->paginate(15);
            return response()->json($logs);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show details of a specific activity log.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $log = DB::table('activity_logs')->where('id', $id)->first();
            if (!$log) {
                return response()->json(['error' => 'Log entry not found'], 404);
            }
            return response()->json($log);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
