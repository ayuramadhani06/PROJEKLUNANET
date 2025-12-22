<?php

namespace App\Http\Controllers;

class TrafficFlowController extends Controller
{
    public function index()
    {
        // DEFAULT kolom
        $defaultColumns = [
            'src_ip',
            'dst_ip',
            'protocol',
            'bytes',
            'packets',
        ];

        // SEMUA kolom tersedia
        $allColumns = [
            'first_forwarded',
            'last_forwarded',
            'system_init_time',
            'in_interface',
            'out_interface',
            'src_port',
            'dst_port',
            'ip_tos',
            'gateway',
            'src_mask',
            'dst_mask',
            'tcp_flags',
            'src_mac',
            'dst_mac',
            'nat_src_address',
            'nat_dst_address',
            'nat_src_port',
            'nat_dst_port',
            'ipv6_flow_label',
            'ttl',
            'is_multicast',
            'ip_header_length',
            'ip_total_length',
            'udp_length',
            'tcp_seq',
            'tcp_ack',
            'tcp_window_size',
            'igmp_type',
            'icmp_type',
            'icmp_code',
        ];

        // DUMMY DATA FLOW
        $flows = [
            [
                'src_ip' => '10.10.15.50',
                'dst_ip' => '8.8.8.8',
                'protocol' => 'UDP',
                'bytes' => 15432,
                'packets' => 32,
                'ttl' => 64,
                'src_port' => 5353,
                'dst_port' => 53,
                'tcp_flags' => '-',
            ],
            [
                'src_ip' => '2001:df4::1',
                'dst_ip' => '2404:6800:4003::65',
                'protocol' => 'TCP',
                'bytes' => 892312,
                'packets' => 87,
                'ttl' => 128,
                'src_port' => 44321,
                'dst_port' => 443,
                'tcp_flags' => 'SYN,ACK',
            ],
        ];

        return view('be.monitor', compact(
            'defaultColumns',
            'allColumns',
            'flows'
        ));
    }
}
