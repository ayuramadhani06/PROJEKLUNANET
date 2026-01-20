<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function downloadPDF()
    {
        // 1. Ambil data akumulasi dengan SUM agar interface tidak duplikat
        $stats = DB::table('agg_interface_stats')
                    ->select(
                        'if_name', 
                        'router_ip', 
                        DB::raw('SUM(rx_bytes) as rx_bytes'), 
                        DB::raw('SUM(tx_bytes) as tx_bytes')
                    )
                    ->groupBy('if_name', 'router_ip')
                    ->orderBy('rx_bytes', 'desc')
                    ->get();

        // 2. Ambil riwayat harian (sama seperti sebelumnya)
        $dailyHistory = DB::table('agg_interface_daily')
                            ->orderBy('time_bucket', 'desc')
                            ->limit(10)
                            ->get();

        $pdf = Pdf::loadView('be.reports', [
            'stats' => $stats,
            'history' => $dailyHistory,
            'date' => date('d F Y')
        ]);

        return $pdf->download('laporan_trafik_jaringan.pdf');
    }
}