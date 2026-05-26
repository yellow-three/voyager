<?php

namespace YellowThree\Voyager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\JsonResponse;

class UpgradeController extends Controller
{
    /**
     * Get upgrade configuration check status via API.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return $this->status();
    }

    /**
     * Return the current upgrade status / readiness.
     */
    public function status(): JsonResponse
    {
        return response()->json([
            'status'  => 'ready',
            'package' => 'yellow-three/voyager',
            'version' => '3.0.0-alpha',
        ]);
    }

    /**
     * Trigger upgrade steps programmatically via API.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function run(Request $request): JsonResponse
    {
        try {
            $exitCode = Artisan::call('voyager:upgrade');

            return response()->json([
                'success' => $exitCode === 0,
                'output'  => Artisan::output(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Alias for run() — keeps resource-style controllers compatible.
     */
    public function store(Request $request): JsonResponse
    {
        return $this->run($request);
    }
}

