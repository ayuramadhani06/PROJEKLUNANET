<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function index()
    {
        // ================= CARD =================
        $totalTraffic = 125438921; // dummy bytes

        // ================= CHART =================
        $topDest = collect([
            ['destination' => 'google.com', 'total_bytes' => 54231234],
            ['destination' => 'facebook.com', 'total_bytes' => 32123411],
            ['destination' => 'cloudflare.com', 'total_bytes' => 21341234],
            ['destination' => 'youtube.com', 'total_bytes' => 12312312],
        ]);

        // ================= TABLE TOP IP =================
        $topIps = [
            ['ip' => '10.10.15.50', 'bytes' => 54231234],
            ['ip' => '2001:df4:3a40::1', 'bytes' => 42123411],
            ['ip' => '160.30.224.7', 'bytes' => 31234123],
            ['ip' => '2404:6800:4003::65', 'bytes' => 21231234],
        ];

        // ================= LIVE TRAFFIC =================
        $liveFlows = [
            [
                'src_ip' => '10.10.15.50',
                'dst_ip' => '160.30.224.7',
                'packets' => 124,
                'dst_hostname' => 'google.com',
            ],
            [
                'src_ip' => '2001:df4:3a40::1',
                'dst_ip' => '2404:6800:4003::65',
                'packets' => 87,
                'dst_hostname' => 'youtube.com',
            ],
            [
                'src_ip' => '172.30.224.10',
                'dst_ip' => '8.8.8.8',
                'packets' => 32,
                'dst_hostname' => null,
            ],
        ];

        return view('be.dashboard', compact(
            'totalTraffic',
            'topDest',
            'topIps',
            'liveFlows'
        ));
    }
}
