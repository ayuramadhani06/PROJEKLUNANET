<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrafficFlowController extends Controller
{
    public function index(Request $request)
    {
        // ambil input user, default = 5
        $perPage = (int) $request->get('per_page', 5);

        // proteksi: minimal 1, maksimal 500 (boleh kita ubah)
        if ($perPage < 1) {
            $perPage = 5;
        }

        if ($perPage > 500) {
            $perPage = 500;
        }

        $search = $request->get('search');

        $defaultColumns = [
            'src_ip',
            'dst_ip',
            'protocol',
            'bytes',
            'packets',
        ];

        $allColumns = [
            'src_port','dst_port','in_interface','out_interface',
            'first_forwarded_abs','last_forwarded_abs','system_init_time',
            'gateway','ip_tos','tcp_flags','ttl','ip_header_length',
            'ip_total_length','udp_length','tcp_seq_num','tcp_ack_num',
            'tcp_window_size','icmp_type','igmp_type','ipv6_flow_label',
            'is_multicast','src_mac','dst_mac','src_mask','dst_mask',
            'nat_src_ip','nat_dst_ip','nat_src_port','nat_dst_port',
            'dst_hostname','dst_asn','dst_org','time',
        ];

        //session presistence
        if ($request->has('columns')) {
            // user memilih kolom → simpan ke session
            session([
                'traffic_columns' => $request->input('columns')
            ]);
        }

        // ambil kolom session atau default
        $selectedColumns = session('traffic_columns', $defaultColumns);
        //ini sebagai tanda kalo datanya munculnya berdasarkan data yang paling baru
        // $query = DB::table('netflow_data')
        //     ->orderByDesc('time');
        
        // INI BUAT KALO SEMISAL IP NYA SUDAH DI DAFTARKAN DI MIKROTIK (TAPI INI BELUM)
        $query = DB::table('netflow_data as n')
            ->leftJoin('interface_mappings as inmap', function ($join) {
                $join->on('n.router_ip', '=', 'inmap.router_ip')
                    ->on('n.in_interface', '=', 'inmap.if_index');
            })
            ->leftJoin('interface_mappings as outmap', function ($join) {
                $join->on('n.router_ip', '=', 'outmap.router_ip')
                    ->on('n.out_interface', '=', 'outmap.if_index');
            })
            ->select([
                'n.*', 

                DB::raw("
                    COALESCE(inmap.if_name, 'ifIndex ' || n.in_interface::text)
                    AS in_interface
                "),

                DB::raw("
                    COALESCE(outmap.if_name, 'ifIndex ' || n.out_interface::text)
                    AS out_interface
                "),
            ])
            ->orderByDesc('n.time');

        
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('src_ip', 'like', "%{$search}%")
                  ->orWhere('dst_ip', 'like', "%{$search}%")
                  ->orWhere('protocol', 'like', "%{$search}%")
                  ->orWhere('dst_hostname', 'like', "%{$search}%")
                  ->orWhere('dst_org', 'like', "%{$search}%");
            });
        }

        $flows = $query
            ->paginate($perPage)->appends($request->query()); // kalau nanti datanya besar banget, ganti limit() -> paginate()
            


        //konvert protocol number to name
        $protocolMap = [
            1  => 'ICMP',
            2  => 'IGMP',
            6  => 'TCP',
            17 => 'UDP',
            41 => 'IPv6',
            47 => 'GRE',
            50 => 'ESP',
            51 => 'AH',
            58 => 'ICMPv6',
            89 => 'OSPF',
            // tambahkan protokol lain sesuai kebutuhan (kalau memang ada lagi)
        ];

        // --- CONVERT BYTES TO HUMAN READABLE ---
        function humanBytes($bytes, $decimals = 2) {
            $size = ['B','KB','MB','GB','TB','PB'];
            $factor = floor((strlen($bytes) - 1) / 3);
            if ($factor == 0) return $bytes . ' B';
            return sprintf("%.{$decimals}f %s", $bytes / pow(1024, $factor), $size[$factor]);
        }

        
        foreach ($flows as $flow) {
            if (isset($flow->protocol) && is_numeric($flow->protocol)) {
                $flow->protocol = $protocolMap[(int)$flow->protocol] ?? 'OTHER';
            }
            // Convert bytes
            if (isset($flow->bytes) && is_numeric($flow->bytes)) {
                $flow->bytes = humanBytes($flow->bytes);
            }
        }

        return view('be.monitor', compact(
            'defaultColumns',
            'allColumns',
            'selectedColumns',
            'flows',
            'perPage'
        ));
    }
}

