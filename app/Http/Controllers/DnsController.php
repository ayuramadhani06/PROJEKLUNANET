<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DnsController extends Controller
{
    public function index()
    {
        return view('be.dns');  
    }

    public function api(Request $request) 
    {
        try {
            $search = $request->get('search');
            $date = $request->get('date');

            // Query untuk Tabel
            $query = DB::table('dns_sessions_enriched')
                    ->where('base_domain', '!=', 'Infrastructure/Resources');

            if (!empty($date)) {
                $query->whereDate('log_time', $date);
            }

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('hostname', 'like', "%{$search}%")
                    ->orWhere('base_domain', 'like', "%{$search}%")
                    ->orWhere('src_ip', 'like', "%{$search}%")
                    ->orWhere('mac', 'like', "%{$search}%");
                });
            }

            $logs = $query->orderBy('log_time', 'desc')
                        ->limit(15)
                        ->get();

            // --- TAMBAHKAN INI ---
            // Menghitung seluruh isi database tanpa filter agar tahu jumlah aslinya
            $totalDatabase = DB::table('dns_sessions_enriched')->count();
            // ---------------------

            // Statistik
            $statsQuery = DB::table('dns_sessions_enriched')
                            ->where('base_domain', '!=', 'Infrastructure/Resources');
            
            if (!empty($date)) {
                $statsQuery->whereDate('log_time', $date);
            }

            $stats = [
                'total_query' => (clone $statsQuery)->sum('total_hits') ?? 0,
                'active_ip'   => (clone $statsQuery)->distinct('src_ip')->count('src_ip'),
                'top_domain'  => (clone $statsQuery)->orderBy('total_hits', 'desc')->value('base_domain') ?? 'N/A'
            ];

            return response()->json([
                'success' => true,
                'logs'    => $logs,
                'stats'   => $stats,
                'total_all' => $totalDatabase // --- TAMBAHKAN INI ---
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}