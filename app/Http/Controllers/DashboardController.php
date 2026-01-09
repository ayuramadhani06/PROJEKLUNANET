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
        $last5Min = $now->copy()->subMinutes(5);
        $sevenDaysAgo = $now->copy()->subDays(7);

        // --- 1. TOTAL TRAFFIC (AMBIL DARI AGGREGATION INTERFACE) ---
        // Kita hitung total rx_bytes + tx_bytes dari semua interface
        $totalBytes = DB::table('agg_interface_stats')->sum(DB::raw('rx_bytes + tx_bytes')) ?: 0;
        
        if ($totalBytes >= 1073741824) {
            $totalTrafficHuman = round($totalBytes / 1073741824, 2) . ' GB';
        } else {
            $totalTrafficHuman = round($totalBytes / 1048576, 2) . ' MB';
        }

        // --- 2. STATS REAL-TIME (TETAP DARI RAW DATA - UNTUK LIVE FEEL) ---
        $activeIps = DB::table('netflow_data')->where('time', '>=', $last5Min)->distinct('src_ip')->count('src_ip');
        $totalPacketsLastMin = DB::table('netflow_data')->where('time', '>=', $now->copy()->subMinute())->sum('packets') ?: 0;
        $pps = round($totalPacketsLastMin / 60);
        $activeFlows = DB::table('netflow_data')->where('time', '>=', $last5Min)->count();

        // --- 3. GRAFIK 7 HARI TERAKHIR (DARI AGGREGATION INTERFACE) ---
        $dailyTraffic = DB::table('agg_interface_stats')
            ->select(DB::raw("DATE(time_bucket) as date"), DB::raw("SUM(rx_bytes + tx_bytes) as total_bytes"))
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

        // --- 4. TOP IP SOURCES (DARI AGGREGATION HOST) ---
        $topIps = DB::table('agg_host_stats')
            ->select('ip_address as ip', DB::raw("SUM(total_bytes_out) as bytes"))
            ->groupBy('ip_address')
            ->orderByDesc('bytes')
            ->limit(5)
            ->get();

        // --- 5. LIVE FLOWS (TETAP DARI RAW DATA) ---
        $liveFlows = DB::table('netflow_data')
            ->select('src_ip', 'dst_ip', 'packets', 'dst_hostname')
            ->orderByDesc('time')
            ->limit(10)
            ->get();

        // --- 6. PROTOCOLS (DARI AGGREGATION APP) ---
        $protocolMap = [1 => 'ICMP', 6 => 'TCP', 17 => 'UDP', 47 => 'GRE', 50 => 'ESP', 89 => 'OSPF'];

        $protocols = DB::table('agg_app_stats')
            ->select(
                'protocol',
                DB::raw('SUM(total_bytes) as bytes'),
                DB::raw('SUM(total_packets) as packets')
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

        // Ambil daftar interface unik dari tabel aggregasi
        //Di sini proses mencocokkan 'Idx: 1' dari tabel Agg dengan angka '1' di tabel Mapping
        $interfaceList = DB::table('agg_interface_stats as agg')
            ->leftJoin('interface_mappings as map', function($join) {
                $join->on('agg.router_ip', '=', 'map.router_ip')
                
                    /** * PROSES "PEMBEDAHAN" TEKS: 
                     * 1. regexp_replace(agg.if_name, '^Idx: ', '') -> 'Idx: 5' jadi '5'
                     * 2. NULLIF(..., '') -> Jaga-jaga kalau hasilnya kosong agar tidak error
                     * 3. CAST(... AS INTEGER) -> Ubah teks '5' jadi angka 5 agar bisa nyambung ke map.if_index
                     */
                    ->on(DB::raw("CAST(NULLIF(regexp_replace(agg.if_name, '^Idx: ', ''), '') AS INTEGER)"), '=', 'map.if_index');
            })
            ->select([
                'agg.if_name', // Ini akan berisi "Idx: 1", "Idx: 2", (identitas asli dari agg)
                DB::raw("
                    CASE 
                        WHEN agg.if_name = 'Idx: 0' THEN 'Internal'
                        ELSE COALESCE(map.if_name, agg.if_name) 
                    END as if_name_display
                ")
            ])
            ->distinct()
            ->whereNotNull('agg.if_name')
            ->orderBy('if_name_display', 'ASC')
            ->get();

        return view('be.dashboard', compact(
            'totalTrafficHuman', 'activeIps', 'totalPacketsLastMin', 
            'pps', 'activeFlows', 'chartLabels', 'chartData', 
            'topIps', 'liveFlows', 'protocols', 'interfaceList'
        ));
    }


    /**
     * AJAX: Digunakan oleh Dashboard untuk update angka kartu (Card) setiap 5 detik
     * tanpa perlu refresh satu halaman penuh.
     */
    public function getLiveStats()
    {
        $now = Carbon::now();
        $last5Min = $now->copy()->subMinutes(5);

        // Stats Cepat (Raw Data)
        $activeIps = DB::table('netflow_data')->where('time', '>=', $last5Min)->distinct('src_ip')->count('src_ip');
        $totalPacketsRaw = DB::table('netflow_data')->where('time', '>=', $now->copy()->subMinute())->sum('packets') ?: 0;
        $pps = round($totalPacketsRaw / 60);
        $activeFlows = DB::table('netflow_data')->where('time', '>=', $last5Min)->count();

        // Total Traffic (Aggregation - Biar gak berat query SUM-nya)
        $totalBytes = DB::table('agg_interface_stats')->sum(DB::raw('rx_bytes + tx_bytes')) ?: 0;
        $totalTrafficHuman = ($totalBytes >= 1073741824) 
            ? round($totalBytes / 1073741824, 2) . ' GB' 
            : round($totalBytes / 1048576, 2) . ' MB';

        return response()->json([
            'activeIps'    => number_format($activeIps),
            'totalPackets' => number_format($totalPacketsRaw),
            'totalTraffic' => $totalTrafficHuman,
            'pps'          => number_format($pps),
            'activeFlows'  => number_format($activeFlows),
        ]);
    }


    /**
     * AJAX: Mendapatkan data history traffic untuk sebuah interface
     * berdasarkan rentang waktu yang dipilih.
     */
    public function getInterfaceHistory(Request $request)
    {
        $ifName = $request->query('if_name'); //Berisi "Idx: 1" atau "Internal"
        $range = $request->query('range', '1h'); 
        
        $isInternal = ($ifName === 'Internal' || $ifName === 'Idx: 0');
        
        $labels = [];
        $rxData = [];
        $txData = [];

        switch ($range) {
            // DURASI PENDEK AMBIL DARI RAW DATA
            case '5m':
            case '15m':
            case '30m':
                $minutes = (int)$range; 
                $query = DB::table('netflow_data')
                    ->select(
                        DB::raw("date_trunc('minute', \"time\") as bucket"),
                        //Filter Inbound: Jika nama interface cocok dengan input user
                        DB::raw("SUM(bytes) FILTER (WHERE in_ifname = '$ifName' OR 'Idx: ' || in_interface = '$ifName') as rx"),
                        //Filter Outbound: Jika nama interface cocok dengan input user
                        DB::raw("SUM(bytes) FILTER (WHERE out_ifname = '$ifName' OR 'Idx: ' || out_interface = '$ifName') as tx")
                    )
                    ->where('time', '>=', now()->subMinutes($minutes))
                    ->groupBy('bucket')
                    ->orderBy('bucket', 'ASC');
                break;

            // Jika durasi panjang, ambil dari tabel RINGKASAN (Aggregated Hourly/Daily)
            // Ini sangat penting agar server tidak "hang" saat user minta data 7 hari
            case '1h':
            case '3h':
            case '6h':
            case '12h':
            case '1d':
                $hours = ($range === '1d') ? 24 : (int)$range;
                $query = DB::table('agg_interface_hourly') // Menggunakan tabel hourly
                    ->select('time_bucket as bucket', 'rx_bytes as rx', 'tx_bytes as tx')
                    ->where('if_name', $ifName)
                    ->where('time_bucket', '>=', now()->subHours($hours))
                    ->orderBy('time_bucket', 'ASC');
                break;

            case '7d':
                $query = DB::table('agg_interface_daily')
                    ->select('time_bucket as bucket', 'rx_bytes as rx', 'tx_bytes as tx')
                    ->where('if_name', $ifName)
                    ->where('time_bucket', '>=', now()->subDays(7))
                    ->orderBy('time_bucket', 'ASC');
                break;

            default:
                return response()->json(['error' => 'Invalid range'], 400);
        }

        $history = $query->get();

        foreach ($history as $row) {
            // Jika rentang harian, munculkan format Tanggal, jika menit munculkan Jam:Menit
            $format = in_array($range, ['1d', '7d']) ? 'd M H:i' : 'H:i';
            $labels[] = Carbon::parse($row->bucket)->format($format);
            
            $rxData[] = (int)($row->rx ?? 0);
            $txData[] = (int)($row->tx ?? 0);
        }

        return response()->json([
            'labels' => $labels,
            'rx_data' => $rxData,
            'tx_data' => $txData,
            'stats' => [
                //Menghitung statistik untuk tabel legend di bawah grafik
                'in_last'  => end($rxData) ?: 0,
                'in_min'   => count($rxData) ? min($rxData) : 0,
                'in_max'   => count($rxData) ? max($rxData) : 0,
                'in_avg'   => count($rxData) ? array_sum($rxData) / count($rxData) : 0,
                'out_last' => end($txData) ?: 0,
                'out_min'  => count($txData) ? min($txData) : 0,
                'out_max'  => count($txData) ? max($txData) : 0,
                'out_avg'  => count($txData) ? array_sum($txData) / count($txData) : 0,
            ]
        ]);
    }
}