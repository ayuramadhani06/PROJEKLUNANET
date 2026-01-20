<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Trafik NetFlow</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; line-height: 1.5; }
        .header { text-align: center; border-bottom: 2px solid #8b0000; padding-bottom: 10px; margin-bottom: 20px; }
        .title { font-size: 22px; font-weight: bold; color: #8b0000; text-transform: uppercase; }
        .info { font-size: 12px; color: #666; }
        
        h4 { border-left: 5px solid #4a0e4e; padding-left: 10px; margin-top: 30px; color: #4a0e4e; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 11px; }
        th { background-color: #8b0000; color: white; padding: 10px; text-align: left; border: 1px solid #700000; }
        td { border: 1px solid #ddd; padding: 8px; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        
        .footer { margin-top: 50px; font-size: 10px; text-align: center; color: #999; }
        .summary-box { background: #fef1f1; border: 1px solid #ffcccc; padding: 15px; border-radius: 5px; margin-top: 20px; font-size: 12px; }
    </style>
</head>
<body>

    <div class="header">
        <div class="title">Laporan Monitoring Trafik Jaringan</div>
        <div class="info">Dicetak pada: {{ $date }} | Sistem NetFlow Advanced Monitoring</div>
    </div>

    <h4>1. Akumulasi Trafik Interface (Total)</h4>
    <table>
        <thead>
            <tr>
                <th>Interface</th>
                <th>Router IP</th>
                <th>Download (RX)</th>
                <th>Upload (TX)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stats as $s)
            <tr>
                <td><strong>{{ $s->if_name }}</strong></td>
                <td>{{ $s->router_ip }}</td>
                {{-- Menampilkan dalam GB --}}
                <td>{{ number_format($s->rx_bytes / (1024*1024*1024), 2) }} GB</td>
                <td>{{ number_format($s->tx_bytes / (1024*1024*1024), 2) }} GB</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h4>2. Riwayat Trafik 7 Hari Terakhir</h4>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Interface</th>
                <th>Download (RX)</th>
                <th>Upload (TX)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($history as $h)
            <tr>
                {{-- PERBAIKAN: Gunakan $h dan time_bucket --}}
                <td>{{ date('d M Y', strtotime($h->time_bucket)) }}</td>
                <td>{{ $h->if_name }}</td>
                <td>{{ number_format($h->rx_bytes / (1024*1024), 2) }} MB</td>
                <td>{{ number_format($h->tx_bytes / (1024*1024), 2) }} MB</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align:center;">Data riwayat harian belum tersedia.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary-box">
        <strong>Kesimpulan Sistem:</strong><br>
        Laporan ini digenerate secara otomatis berdasarkan data agregasi harian. Seluruh data di atas mencerminkan penggunaan bandwidth pada jalur masuk (RX) dan keluar (TX) untuk masing-masing interface yang terdeteksi pada perangkat router.
    </div>

    <div class="footer">
        &copy; {{ date('Y') }} Network Monitoring System - Dokumen ini sah dan dihasilkan secara otomatis oleh sistem.
    </div>

</body>
</html>