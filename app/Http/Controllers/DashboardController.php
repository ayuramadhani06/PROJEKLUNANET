<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        /* ================= TOTAL TRAFFIC ================= */
        $totalTraffic = DB::table('netflow_data')
            ->sum('bytes');

        /* ================= TOP DESTINATION (CHART) ================= */
        $topDest = DB::table('netflow_data')
            ->select(
                DB::raw("COALESCE(dst_hostname, dst_ip::text) as destination"),
                DB::raw("SUM(bytes) as total_bytes")
            )
            ->groupBy('destination')
            ->orderByDesc('total_bytes')
            ->limit(5)
            ->get();

        /* ================= TOP IP BY TRAFFIC ================= */
        $topIps = DB::table('netflow_data')
            ->select(
                DB::raw("src_ip::text as ip"),
                DB::raw("SUM(bytes) as bytes")
            )
            ->groupBy('src_ip')
            ->orderByDesc('bytes')
            ->limit(5)
            ->get();

        /* ================= LIVE TRAFFIC ================= */
        $liveFlows = DB::table('netflow_data')
            ->select(
                DB::raw("src_ip::text as src_ip"),
                DB::raw("dst_ip::text as dst_ip"),
                'packets',
                'dst_hostname'
            )
            ->orderByDesc('time')
            ->limit(10)
            ->get();

        return view('be.dashboard', compact(
            'totalTraffic',
            'topDest',
            'topIps',
            'liveFlows'
        ));
    }
}
