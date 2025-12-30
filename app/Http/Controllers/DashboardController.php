<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $lastMinute = $now->copy()->subMinute();
        $last5Min   = $now->copy()->subMinutes(5);

        /* ================= TOTAL TRAFFIC (AUTO MB / GB) ================= */
        $totalBytes = DB::table('netflow_data')->sum('bytes');

        if ($totalBytes >= 1024 * 1024 * 1024) {
            // GB
            $totalTrafficHuman = round($totalBytes / 1024 / 1024 / 1024, 2) . ' GB';
        } else {
            // MB
            $totalTrafficHuman = round($totalBytes / 1024 / 1024, 2) . ' MB';
        }


        /* ================= ACTIVE IP (LAST 5 MIN) ================= */
        $activeIps = DB::table('netflow_data')
            ->where('time', '>=', $last5Min)
            ->distinct('src_ip')
            ->count('src_ip');

        /* ================= TOTAL PACKETS (LAST 1 MIN) ================= */
        $totalPackets = DB::table('netflow_data')
            ->where('time', '>=', $lastMinute)
            ->sum('packets');

        /* ================= PACKETS PER SECOND ================= */
        $pps = round($totalPackets / 60);

        /* ================= ACTIVE FLOWS (LAST 5 MIN) ================= */
        $activeFlows = DB::table('netflow_data')
            ->where('time', '>=', $last5Min)
            ->count();

        /* ================= TOP DEST ================= */
        $topDest = DB::table('netflow_data')
            ->select(
                DB::raw("COALESCE(dst_hostname, dst_ip::text) as destination"),
                DB::raw("SUM(bytes) as total_bytes")
            )
            ->groupBy('destination')
            ->orderByDesc('total_bytes')
            ->limit(5)
            ->get();

        /* ================= TOP IP ================= */
        $topIps = DB::table('netflow_data')
            ->select(
                DB::raw("src_ip::text as ip"),
                DB::raw("SUM(bytes) as bytes")
            )
            ->groupBy('src_ip')
            ->orderByDesc('bytes')
            ->limit(5)
            ->get();

        /* ================= LIVE FLOW ================= */
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

        /* ================= PROTOCOLS (BY BYTES & PACKETS) ================= */
        $protocolMap = [
            1  => 'ICMP',
            6  => 'TCP',
            17 => 'UDP',
        ];

        $protocolAgg = DB::table('netflow_data')
            ->select('protocol', DB::raw('SUM(bytes) as bytes'), DB::raw('SUM(packets) as packets'))
            ->groupBy('protocol')
            ->get();

        $protocolLabels = [];
        $protocolBytes = [];
        $protocolPackets = [];

        foreach ($protocolAgg as $p) {
            $name = is_numeric($p->protocol) ? ($protocolMap[(int)$p->protocol] ?? 'OTHER') : ($p->protocol ?: 'OTHER');
            $protocolLabels[] = $name;
            $protocolBytes[] = (int) $p->bytes;
            $protocolPackets[] = (int) $p->packets;
        }

        // specific quick stats
        $tcpPackets = DB::table('netflow_data')->where('protocol', 6)->sum('packets');
        $tcpBytes   = DB::table('netflow_data')->where('protocol', 6)->sum('bytes');

        $udpPackets = DB::table('netflow_data')->where('protocol', 17)->sum('packets');
        $udpBytes   = DB::table('netflow_data')->where('protocol', 17)->sum('bytes');

        $icmpPackets = DB::table('netflow_data')->where('protocol', 1)->sum('packets');
        $icmpBytes   = DB::table('netflow_data')->where('protocol', 1)->sum('bytes');

        // human readable
        $formatBytes = function ($bytes) {
            if ($bytes >= 1024 * 1024 * 1024) {
                return round($bytes / 1024 / 1024 / 1024, 2) . ' GB';
            }
            return round($bytes / 1024 / 1024, 2) . ' MB';
        };

        $tcpBytesHuman  = $formatBytes($tcpBytes);
        $udpBytesHuman  = $formatBytes($udpBytes);
        $icmpBytesHuman = $formatBytes($icmpBytes);

        return view('be.dashboard', compact(
            'totalTrafficHuman',
            'activeIps',
            'totalPackets',
            'pps',
            'activeFlows',
            'topDest',
            'topIps',
            'liveFlows',
            'protocolLabels',
            'protocolBytes',
            'protocolPackets',
            'tcpPackets',
            'tcpBytesHuman',
            'udpPackets',
            'udpBytesHuman',
            'icmpPackets',
            'icmpBytesHuman'
        ));
    }
}
