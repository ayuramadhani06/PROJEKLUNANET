<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DnsController extends Controller
{
    public function index()
    {
        return view('be.dns');  
    }

    public function api(Request $request) 
    {
        try {
            $search = strtolower($request->get('search'));
            $date = $request->get('date');
            $page = $request->get('page', 1);
            $onlyDhcp = $request->get('only_dhcp', 'false') === 'true';
            $perPage = 15; 
            $offset = ($page - 1) * $perPage;

            // --- 1. PROSES NOISE LIST DARI FILE ---
            $noiseList = [];
            $filePath = base_path('noise_list.txt');
            if (file_exists($filePath)) {
                $content = file_get_contents($filePath);
                // Pecah berdasarkan koma, bersihkan spasi/newline
                $rawNoise = explode(',', str_replace(["\n", "\r"], '', $content));
                $noiseList = array_filter(array_map('trim', $rawNoise));
            }

            // --- 2. QUERY UTAMA ---
            $query = DB::table('dns_sessions_enriched as dns')
                        ->where('dns.base_domain', '!=', 'Infrastructure/Resources');

            // --- 3. APPLY NOISE FILTER ---
            if (!empty($noiseList)) {
                $query->where(function($q) use ($noiseList) {
                    foreach ($noiseList as $noise) {
                        $q->where('dns.base_domain', 'NOT LIKE', "%$noise%");
                    }
                });
            }

            // --- 4. LOGIKA SEARCH (Identity, Network, Domain, Status) ---
            if (!empty($search)) {
                if ($search === 'online') {
                    // Ambil waktu data paling baru sebagai acuan 'Online'
                    $latestTime = DB::table('dns_sessions_enriched')->max('log_time');
                    $threshold = Carbon::parse($latestTime)->subMinutes(1);
                    $query->where('dns.log_time', '>=', $threshold);
                } elseif ($search === 'offline') {
                    $latestTime = DB::table('dns_sessions_enriched')->max('log_time');
                    $threshold = Carbon::parse($latestTime)->subMinutes(1);
                    $query->where('dns.log_time', '<', $threshold);
                } else {
                    $query->where(function ($q) use ($search) {
                        $q->where('dns.hostname', 'like', "%{$search}%")      // Identity
                          ->orWhere('dns.base_domain', 'like', "%{$search}%") // Base Domain
                          ->orWhere('dns.src_ip', 'like', "%{$search}%")      // Network Address (IP)
                          ->orWhere('dns.mac', 'like', "%{$search}%");       // Network Address (MAC)
                    });
                }
            }

            // --- 5. FILTER TAMBAHAN (DHCP & DATE) ---
            if ($onlyDhcp) {
                $query->whereExists(function ($q) {
                    $q->select(DB::raw(1))
                    ->from('dhcp_leases')
                    ->whereRaw('dhcp_leases.ip = dns.src_ip');
                });
            }

            if (!empty($date)) {
                $query->whereDate('dns.log_time', $date);
            }

            // Hitung Total setelah semua filter diaplikasikan
            $totalFiltered = $query->count();
            $totalPage = ceil($totalFiltered / $perPage);

            // Ambil data dengan Pagination
            $latestDataInDb = DB::table('dns_sessions_enriched')->max('log_time');
            $latestTime = Carbon::parse($latestDataInDb);

            $logs = $query->orderBy('dns.log_time', 'desc')
                        ->offset($offset)
                        ->limit($perPage)
                        ->get()
                        ->map(function($log) use ($latestTime) {
                            $lastSeen = Carbon::parse($log->log_time);
                            $log->is_online = $lastSeen->diffInMinutes($latestTime) <= 1;
                            $log->time_ago = $lastSeen->diffForHumans();
                            return $log;
                        });

            // --- 6. QUERY STATS (Harus ikut filter noise & search agar sinkron) ---
            $statsQuery = clone $query; 
            
            $stats = [
                'total_query' => (clone $statsQuery)->sum('dns.total_hits') ?? 0,
                'active_ip'   => (clone $statsQuery)->distinct('dns.src_ip')->count('dns.src_ip'),
                'top_domain'  => (clone $statsQuery)->orderBy('dns.total_hits', 'desc')->value('dns.base_domain') ?? 'N/A'
            ];

            return response()->json([
                'success' => true,
                'logs'    => $logs,
                'stats'   => $stats,
                'current_page' => (int)$page,
                'total_page'   => $totalPage,
                'total_filtered' => $totalFiltered
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}