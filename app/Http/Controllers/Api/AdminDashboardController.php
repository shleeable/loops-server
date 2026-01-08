<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AdminDashboardService;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    protected AdminDashboardService $dashboardService;

    public function __construct(AdminDashboardService $dashboardService)
    {
        $this->middleware('auth');
        $this->dashboardService = $dashboardService;
    }

    public function index(Request $request)
    {
        $shouldRefresh = $request->has('refresh') && $request->boolean('refresh');
        $periodParam = $request->input('period', '30d');

        $data = $this->dashboardService->getDashboardData($periodParam, $shouldRefresh, $shouldRefresh);

        return response()->json([
            'data' => $data,
        ]);
    }
}
