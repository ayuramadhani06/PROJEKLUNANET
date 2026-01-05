<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $lastMinute = $now->copy()->subMinute();
        $last5Min   = $now->copy()->subMinutes(5);
        $sevenDaysAgo = $now->copy()->subDays(7);

        // --- 1. TOTAL TRAFFIC (DARI SUMMARY) ---
        $totalBytes = DB::table('netflow_summary')->sum('bytes') ?: 0;
        if ($totalBytes >= 1073741824) {
            $totalTrafficHuman = round($totalBytes / 1073741824, 2) . ' GB';
        } else {
            $totalTrafficHuman = round($totalBytes / 1048576, 2) . ' MB';
        }

        // --- 2. STATS REAL-TIME (DARI RAW DATA) ---
        $activeIps = DB::table('netflow_data')->where('time', '>=', $last5Min)->distinct('src_ip')->count('src_ip');
        $totalPackets = DB::table('netflow_data')->where('time', '>=', $lastMinute)->sum('packets') ?: 0;
        $pps = round($totalPackets / 60);
        $activeFlows = DB::table('netflow_data')->where('time', '>=', $last5Min)->count();

        // --- 3. GRAFIK 7 HARI TERAKHIR (DARI SUMMARY) ---
        $dailyTraffic = DB::table('netflow_summary')
            ->select(DB::raw("DATE(time_bucket) as date"), DB::raw("SUM(bytes) as total_bytes"))
            ->where('time_bucket', '>=', $sevenDaysAgo)
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        $chartLabels = [];
        $chartData = [];
        foreach ($dailyTraffic as $dt) {
            $chartLabels[] = Carbon::parse($dt->date)->format('M d');
            $chartData[] = $dt->total_bytes;
        }

        // --- 4. TOP IP SOURCES (DARI SUMMARY) ---
        $topIps = DB::table('netflow_summary')
            ->select(DB::raw("src_ip::text as ip"), DB::raw("SUM(bytes) as bytes"))
            ->groupBy('src_ip')
            ->orderByDesc('bytes')
            ->limit(5)
            ->get();

        // --- 5. LIVE FLOWS (DARI RAW DATA) ---
        $liveFlows = DB::table('netflow_data')
            ->select('src_ip', 'dst_ip', 'packets', 'dst_hostname')
            ->orderByDesc('time')
            ->limit(10)
            ->get();

        // --- 6. PROTOCOLS (DARI SUMMARY) ---
        $protocolMap = [
            1   => 'ICMP',
            6   => 'TCP',
            17  => 'UDP',
            47  => 'GRE',
            50  => 'ESP',
            51  => 'AH',
            89  => 'OSPF',
            132 => 'SCTP',
        ];

        $protocols = DB::table('netflow_data')
            ->where('time', '>=', $last5Min)
            ->select(
                'protocol',
                DB::raw('SUM(bytes) as bytes'),
                DB::raw('SUM(packets) as packets')
            )
            ->groupBy('protocol')
            ->orderByDesc('bytes')
            ->get()
            ->map(function ($p) use ($protocolMap) {
                return [
                    'id'      => $p->protocol,
                    'name'    => $protocolMap[$p->protocol] ?? 'PROTO ' . $p->protocol,
                    'bytes'   => (int) $p->bytes,
                    'packets' => (int) $p->packets,
                ];
            });


        // --- 7. STATS PER PROTOCOL (UNTUK BADGE/CARD) ---
        $tcp = DB::table('netflow_summary')->where('protocol', 6)->selectRaw('SUM(packets) as p, SUM(bytes) as b')->first();
        $udp = DB::table('netflow_summary')->where('protocol', 17)->selectRaw('SUM(packets) as p, SUM(bytes) as b')->first();
        $icmp = DB::table('netflow_summary')->where('protocol', 1)->selectRaw('SUM(packets) as p, SUM(bytes) as b')->first();

        // RETURN SEMUA KE VIEW
        return view('be.dashboard', compact(
            'totalTrafficHuman',
            'activeIps',
            'totalPackets',
            'pps',
            'activeFlows',
            'chartLabels',
            'chartData',
            'topIps',
            'liveFlows',
            'protocols'
        ));
    }
}