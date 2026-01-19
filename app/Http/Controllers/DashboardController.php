<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Gunakan timezone Asia/Jakarta agar sinkron dengan script aggregator
        $now = Carbon::now('Asia/Jakarta');
        $sevenDaysAgo = $now->copy()->subDays(7);

        // --- 1. TOTAL TRAFFIC ---
        $totalBytes = DB::table('agg_interface_stats')->sum(DB::raw('rx_bytes + tx_bytes')) ?: 0;
        $totalTrafficHuman = $this->formatBytes($totalBytes);

        // --- 2. STATS REAL-TIME ---
        $last5Min = $now->copy()->subMinutes(5);
        $activeIps = DB::table('netflow_data')->where('time', '>=', $last5Min)->distinct('src_ip')->count('src_ip');
        $totalPacketsLastMin = DB::table('netflow_data')->where('time', '>=', $now->copy()->subMinute())->sum('packets') ?: 0;
        $pps = round($totalPacketsLastMin / 60);
        $activeFlows = DB::table('netflow_data')->where('time', '>=', $last5Min)->count();

        // --- 3. GRAFIK UTAMA (7 HARI) ---
        $dailyTraffic = DB::table('agg_interface_daily')
            ->select(DB::raw("time_bucket::date as date"), DB::raw("SUM(rx_bytes + tx_bytes) as total_bytes"))
            ->where('time_bucket', '>=', $sevenDaysAgo)
            ->groupBy('date')->orderBy('date', 'ASC')->get();

        $chartLabels = []; $chartData = [];
        foreach ($dailyTraffic as $dt) {
            $chartLabels[] = Carbon::parse($dt->date)->format('M d');
            $chartData[] = $dt->total_bytes;
        }

        // --- 4. TOP IP SOURCES ---
        $topIps = DB::table('agg_host_stats')
            ->select('ip_address as ip', DB::raw("SUM(total_bytes_out) as bytes"))
            ->groupBy('ip_address')->orderByDesc('bytes')->limit(5)->get();

        // --- 5. LIVE FLOWS ---
        $liveFlows = DB::table('netflow_data')->select('src_ip', 'dst_ip', 'packets', 'in_ifname', 'out_ifname')
            ->orderByDesc('time')->limit(10)->get();

        // --- 6. PROTOCOLS  ---
        $protocolMap = [1 => 'ICMP', 6 => 'TCP', 17 => 'UDP', 47 => 'GRE', 50 => 'ESP', 89 => 'OSPF'];
        $protocols = DB::table('netflow_data')
            ->select('protocol', DB::raw('SUM(bytes) as bytes'), DB::raw('SUM(packets) as packets'))
            ->where('time', '>=', $now->copy()->subHours(24))
            ->groupBy('protocol')->orderByDesc('bytes')->get()
            ->map(function ($p) use ($protocolMap) {
                return (object)[
                    'name' => $protocolMap[$p->protocol] ?? 'PROTO ' . $p->protocol,
                    'bytes' => (int) $p->bytes,
                    'packets' => (int) $p->packets
                ];
            });

        // --- 7. INTERFACE LIST (Hanya yang unik dan bersih) ---
        $interfaceList = DB::table('agg_interface_stats')
            ->select(DB::raw('DISTINCT TRIM(if_name) as if_name'))
            ->whereNotNull('if_name')->where('if_name', '!=', '')
            ->orderBy('if_name', 'ASC')->get();

        return view('be.dashboard', compact(
            'totalTrafficHuman', 'activeIps', 'pps', 'activeFlows', 
            'chartLabels', 'chartData', 'topIps', 'liveFlows', 
            'protocols', 'interfaceList'
        ));
    }

    public function getInterfaceHistory(Request $request)
    {
        $ifName = trim($request->query('if_name'));
        $range = $request->query('range', '1h');
        
        if (!$ifName) return response()->json(['error' => 'No interface selected'], 400);
        
        $now = Carbon::now('Asia/Jakarta');
        $labels = []; $rxData = []; $txData = [];

        // Logic pemilihan tabel: Menit (Raw), Jam (Hourly), Minggu (Stats/Daily)
        if (in_array($range, ['5m', '15m', '30m'])) {
            $minutes = (int)$range;
            $history = DB::table('netflow_data')
                ->select(
                    DB::raw("date_trunc('minute', \"time\") as bucket"),
                    DB::raw("SUM(bytes) FILTER (WHERE TRIM(in_ifname) = ?) as rx"),
                    DB::raw("SUM(bytes) FILTER (WHERE TRIM(out_ifname) = ?) as tx")
                )
                ->setBindings([$ifName, $ifName])
                ->where('time', '>=', $now->copy()->subMinutes($minutes + 1))
                ->groupBy('bucket')->orderBy('bucket', 'ASC')->get();
        } 
        elseif (in_array($range, ['1h', '3h', '6h', '12h', '1d'])) {
            $hours = ($range === '1d') ? 24 : (int)$range;
            $history = DB::table('agg_interface_hourly')
                ->select('time_bucket as bucket', 'rx_bytes as rx', 'tx_bytes as tx')
                ->whereRaw("TRIM(if_name) = ?", [$ifName])
                ->where('time_bucket', '>=', $now->copy()->subHours($hours + 1))
                ->orderBy('time_bucket', 'ASC')->get();
        } 
        else { // 7d
            $history = DB::table('agg_interface_daily')
                ->select('time_bucket as bucket', 'rx_bytes as rx', 'tx_bytes as tx')
                ->whereRaw("TRIM(if_name) = ?", [$ifName])
                ->where('time_bucket', '>=', $now->copy()->subDays(7))
                ->orderBy('time_bucket', 'ASC')->get();
        }

        foreach ($history as $row) {
            $format = in_array($range, ['1d', '7d']) ? 'd M H:i' : 'H:i';
            $labels[] = Carbon::parse($row->bucket)->timezone('Asia/Jakarta')->format($format);
            $rxData[] = (int)($row->rx ?? 0);
            $txData[] = (int)($row->tx ?? 0);
        }

        return response()->json([
            'labels' => $labels,
            'rx_data' => $rxData,
            'tx_data' => $txData,
            'stats' => $this->calculateStats($rxData, $txData)
        ]);
    }

    // Fungsi pembantu lainnya (formatBytes & calculateStats) tetap sama...
    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        return round($bytes / pow(1024, $pow), $precision) . ' ' . $units[$pow];
    }

    private function calculateStats($rx, $tx) {
        return [
            'in_last'  => (int)(end($rx) ?: 0),
            'in_min'   => (int)(count($rx) ? min($rx) : 0),
            'in_max'   => (int)(count($rx) ? max($rx) : 0),
            'in_avg'   => (int)(count($rx) ? array_sum($rx) / count($rx) : 0),
            'out_last' => (int)(end($tx) ?: 0),
            'out_min'  => (int)(count($tx) ? min($tx) : 0),
            'out_max'  => (int)(count($tx) ? max($tx) : 0),
            'out_avg'  => (int)(count($tx) ? array_sum($tx) / count($tx) : 0),
        ];
    }

    // Tambahkan method ini di dalam DashboardController.php

    public function getLiveStats()
    {
        $now = Carbon::now('Asia/Jakarta');
        
        // 1. Stats Real-time (5 Menit Terakhir)
        $last5Min = $now->copy()->subMinutes(5);
        $activeIps = DB::table('netflow_data')
            ->where('time', '>=', $last5Min)
            ->distinct('src_ip')
            ->count('src_ip');

        // 2. PPS (Packets Per Second) - Berdasarkan 1 menit terakhir
        $totalPacketsLastMin = DB::table('netflow_data')
            ->where('time', '>=', $now->copy()->subMinute())
            ->sum('packets') ?: 0;
        $pps = round($totalPacketsLastMin / 60);

        // 3. Active Flows (5 Menit Terakhir)
        $activeFlows = DB::table('netflow_data')
            ->where('time', '>=', $last5Min)
            ->count();

        // 4. Total Traffic (Semua data teragregasi)
        $totalBytes = DB::table('agg_interface_stats')->sum(DB::raw('rx_bytes + tx_bytes')) ?: 0;

        // 5. Total Packets (Kumulatif)
        $totalPacketsAll = DB::table('agg_interface_stats')->sum(DB::raw('rx_packets + tx_packets')) ?: 0;

        return response()->json([
            'activeIps'    => number_format($activeIps),
            'totalPackets' => number_format($totalPacketsAll),
            'totalTraffic' => $this->formatBytes($totalBytes),
            'pps'          => number_format($pps),
            'activeFlows'  => number_format($activeFlows),
        ]);
    }
}