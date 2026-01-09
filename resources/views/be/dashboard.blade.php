@extends('be.master')

@php
  $title = 'Dashboard Traffic';
  $breadcrumb = 'Dashboard';
@endphp

@section('content')

{{-- ================= HEADER ================= --}}
<div class="row mb-4">
  <div class="col-12">
    <div class="card text-white border-0 shadow-lg"
         style="background: linear-gradient(135deg, #8b0000 0%, #4a0e4e 100%); border-radius: 15px;">
      <div class="card-body d-flex justify-content-between align-items-center p-4">
        <div>
          <h4 class="mb-1 fw-bold text-white">Dashboard</h4>
          <p class="text-sm mb-0 opacity-8">Advanced NetFlow monitoring</p>
        </div>

        <div class="d-flex gap-4 text-center">
          <div>
            <h5 id="live-active-ips" class="mb-0 fw-bold text-white">{{ $activeIps ?? 0 }}</h5>
            <span class="text-xs opacity-7">Active IPs</span>
          </div>
          <div class="border-start ps-3">
            <h5 id="live-packets" class="mb-0 fw-bold text-white">{{ number_format($totalPackets ?? 0) }}</h5>
            <span class="text-xs opacity-7">Packets</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ================= STAT CARDS ================= --}}
<div class="row mb-3">
  <div class="col-md-4 mb-3">
    <div class="card"><div class="card-body">
      <p class="text-sm mb-1">Total Traffic</p>
      <h5 id="live-total-traffic" class="fw-bold">{{ $totalTrafficHuman ?? '0 MB' }}</h5>
    </div></div>
  </div>

  <div class="col-md-4 mb-3">
    <div class="card"><div class="card-body">
      <p class="text-sm mb-1">Packets / sec</p>
      <h5 id="live-pps" class="fw-bold">{{ $pps ?? 0 }}</h5>
    </div></div>
  </div>

  <div class="col-md-4 mb-3">
    <div class="card"><div class="card-body">
      <p class="text-sm mb-1">Active Flows</p>
      <h5 id="live-flows" class="fw-bold">{{ $activeFlows ?? 0 }}</h5>
    </div></div>
  </div>
</div>

{{-- ================= TABS ================= --}}
<ul class="nav nav-tabs mb-4">
  <li class="nav-item">
    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#overview">Overview</button>
  </li>
  <li class="nav-item">
    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#protocols">Protocols</button>
  </li>
  <li class="nav-item">
    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#interfaces">Interfaces</button>
  </li>
</ul>

<div class="tab-content">

{{-- ================= OVERVIEW ================= --}}
<div class="tab-pane fade show active" id="overview">
  <div class="row">
    <div class="col-lg-7 mb-3">
      <div class="card h-100">
        <div class="card-header pb-0"><h6>Traffic Volume</h6></div>
        <div class="card-body">
          <canvas id="chart-bars"></canvas>
        </div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card h-100">
        <div class="card-header pb-0"><h6>Top Traffic Sources</h6></div>
        <div class="card-body">
          @foreach($topIps as $ip)
            <div class="d-flex justify-content-between mb-2">
              <span class="fw-bold">{{ $ip->ip }}</span>
              <span>
                @if($ip->bytes >= 1073741824)
                  {{ round($ip->bytes / 1073741824, 2) }} GB
                @else
                  {{ round($ip->bytes / 1048576, 2) }} MB
                @endif
              </span>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ================= PROTOCOLS ================= --}}
<div class="tab-pane fade" id="protocols">
  <div class="row">

    @php
      $collection = collect($protocols);
      $sortedProtocols = $collection->sortByDesc('bytes')->values();
      $totalBytes = $sortedProtocols->sum('bytes') ?: 0;
      $totalPackets = $sortedProtocols->sum('packets') ?: 0;
      $colors = ['#8b0000','#4a0e4e','#506384','#dc2626','#16a34a','#2563eb'];
    @endphp

    {{-- DONUT & DETAILS (donut on top, details below) --}}
    <div class="col-12 mb-3">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h6 class="mb-0">Protocol Distribution</h6>
          <small class="text-muted">{{ number_format($totalPackets) }} packets • @if($totalBytes >= 1073741824){{ round($totalBytes/1073741824,2) }} GB @else {{ round($totalBytes/1048576,2) }} MB @endif</small>
        </div>
        <div class="card-body">

          <div class="mb-4" style="height:320px;">
            <canvas id="protocol-donut"></canvas>
          </div>

          <div class="row g-2">
            @foreach($sortedProtocols as $proto)
            @php
              $bytes = is_array($proto) ? ($proto['bytes'] ?? 0) : ($proto->bytes ?? 0);
              $packets = is_array($proto) ? ($proto['packets'] ?? 0) : ($proto->packets ?? 0);
              $pct = $totalBytes ? round($bytes / $totalBytes * 100, 1) : 0;
              $color = $colors[$loop->index % count($colors)];
              if ($bytes >= 1073741824) $human = round($bytes/1073741824,2).' GB';
              elseif ($bytes >= 1048576) $human = round($bytes/1048576,2).' MB';
              elseif ($bytes >= 1024) $human = round($bytes/1024,2).' KB';
              else $human = $bytes.' B';
            @endphp

            <div class="col-12">
              <div class="d-flex align-items-center justify-content-between p-3 border rounded">
                <div>
                  <div class="text-xs opacity-8">{{ is_array($proto) ? $proto['name'] : $proto->name }} Traffic</div>
                  <div class="fw-bold">{{ number_format($packets) }} packets</div>
                  <div class="text-muted small">{{ $human }}</div>
                  <div class="text-muted small">{{ $pct }}%</div>
                </div>
                <div class="text-end">
                  <span style="display:inline-block;width:12px;height:12px;background:{{ $color }};border-radius:50%;margin-bottom:8px"></span>
                  <div class="mt-1"><small class="text-muted">{{ is_array($proto) ? $proto['name'] : $proto->name }}</small></div>
                </div>
              </div>
            </div>

            @endforeach
          </div>

        </div>
      </div>
    </div>

  </div>
</div>

{{-- ================= INTERFACES ================= --}}
<div class="tab-pane fade" id="interfaces">
  <div class="row">
    <div class="col-12 mb-3">
      <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center bg-light">
          <h6 class="mb-0">Interface Traffic History</h6>
          <div class="d-flex gap-2">
            <select id="select-time-range" class="form-select form-select-sm" style="width: 150px;">
                <option value="5m" selected>Last 5 Minutes</option>
                <option value="15m">Last 15 Minutes</option>
                <option value="30m">Last 30 Minutes</option>
                <option value="1h">Last 1 Hour</option>
                <option value="3h">Last 3 Hours</option>
                <option value="6h">Last 6 Hours</option>
                <option value="12h">Last 12 Hours</option>
                <option value="1d">Last 1 Day</option>
                <option value="7d">last week</option>

            </select>
            <select id="select-interface" class="form-select form-select-sm" style="width: 200px;">
                <option value="">-- Select Interface --</option>
                @foreach($interfaceList as $iface)
                    @php
                        // Pastikan kita mengambil property yang benar
                        $currentValue = $iface->if_name ?? ''; 
                        $currentDisplay = $iface->if_name_display ?? $currentValue;
                    @endphp
                    <option value="{{ $currentValue }}">{{ $currentDisplay }}</option>
                @endforeach
            </select>
          </div>
        </div>
        <div class="card-body">
          <div style="height: 400px; position: relative;">
            <canvas id="chart-interface-zabbix"></canvas>
          </div>
          
          {{-- Legend Style --}}
          <div class="table-responsive mt-4">
            <table class="table table-sm text-center border">
              <thead class="bg-light text-xs">
                <tr>
                  <th>Description</th>
                  <th>Last</th>
                  <th>Min</th>
                  <th>Avg</th>
                  <th>Max</th>
                </tr>
              </thead>
              <tbody class="text-sm" id="zabbix-legend-body">
                <tr>
                  <td class="text-start"><span class="badge" style="background:#16a34a">&nbsp;</span> Inbound Traffic</td>
                  <td id="in-last">-</td>
                  <td id="in-min">-</td>
                  <td id="in-avg">-</td>
                  <td id="in-max">-</td>
                </tr>
                <tr>
                  <td class="text-start"><span class="badge" style="background:#2563eb">&nbsp;</span> Outbound Traffic</td>
                  <td id="out-last">-</td>
                  <td id="out-min">-</td>
                  <td id="out-avg">-</td>
                  <td id="out-max">-</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

</div>
@endsection

@section('script')
<script>
/* ================= TRAFFIC LINE ================= */
const trafficCtx = document.getElementById('chart-bars');
if (trafficCtx) {
  new Chart(trafficCtx, {
    type: 'line',
    data: {
      labels: {!! json_encode($chartLabels ?? []) !!},
      datasets: [{
        label: 'Total Traffic (Bytes)',
        data: {!! json_encode($chartData ?? []) !!},
        borderColor: '#8b0000',
        backgroundColor: 'rgba(139,0,0,0.15)',
        fill: true,
        tension: 0.4
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false
    }
  });
}

/* ================= PROTOCOL DONUT ================= */
const protocols = @json($sortedProtocols);
const protocolColors = @json($colors) || ['#8b0000','#4a0e4e','#506384','#dc2626','#16a34a','#2563eb'];

const donutCtx = document.getElementById('protocol-donut');
if (donutCtx) {
  new Chart(donutCtx, {
    type: 'doughnut',
    data: {
      labels: protocols.map(p => p['name']),
      datasets: [{
        data: protocols.map(p => p['bytes']),
        backgroundColor: protocols.map((p, i) =>
          protocolColors[i % protocolColors.length]
        ),
        borderWidth: 0
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '65%',
      plugins: {
        legend: { display: false }
      }
    }
  });
}


function updateDashboardStats() {
    $.ajax({
        url: "{{ route('dashboard.stats') }}",
        method: 'GET',
        success: function(data) {
            // Update elemen berdasarkan ID
            $('#live-active-ips').text(data.activeIps);
            $('#live-packets').text(data.totalPackets);
            $('#live-total-traffic').text(data.totalTraffic);
            $('#live-pps').text(data.pps);
            $('#live-flows').text(data.activeFlows);
            
            console.log("Stats Updated: " + new Date().toLocaleTimeString());
        },
        error: function(err) {
            console.error("Gagal ambil data live stats", err);
        }
    });
}

// Jalankan tiap 5 detik (5000 ms)
setInterval(updateDashboardStats, 5000);

// Panggil sekali saat halaman baru di-load biar gak nunggu 5 detik pertama
$(document).ready(function() {
    updateDashboardStats();
});


/* ================= INTERFACE CHART & AUTO UPDATE ================= */
const zabbixCtx = document.getElementById('chart-interface-zabbix');
let zabbixChart;

// 1. Inisialisasi Grafik (Kosong dulu)
if (zabbixCtx) {
    zabbixChart = new Chart(zabbixCtx, {
        type: 'line',
        data: {
            labels: [], 
            datasets: [
                {
                    label: 'Incoming',
                    data: [],
                    borderColor: '#16a34a',
                    backgroundColor: 'rgba(22, 163, 74, 0.1)',
                    fill: true,
                    tension: 0.1,
                    pointRadius: 0,
                    borderWidth: 2
                },
                {
                    label: 'Outgoing',
                    data: [],
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    fill: true,
                    tension: 0.1,
                    pointRadius: 0,
                    borderWidth: 2
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Bytes' },
                    ticks: {
                        callback: function(value) { return formatBytes(value); }
                    }
                }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
}

// 2. Fungsi untuk Mengambil Data Grafik Terbaru (Live)
function updateInterfaceChart() {
    const ifName = $('#select-interface').val();
    const timeRange = $('#select-time-range').val();
    
    if(!ifName) return;

    $.ajax({
        url: "{{ route('interface.history') }}",
        data: { if_name: ifName, range: timeRange },
        success: function(res) {
            if (zabbixChart) {
                zabbixChart.data.labels = res.labels;
                zabbixChart.data.datasets[0].data = res.rx_data;
                zabbixChart.data.datasets[1].data = res.tx_data;
                zabbixChart.update('none'); // Update tanpa animasi kasar

                // Update Legend Tabel
                $('#in-last').text(formatBytes(res.stats.in_last));
                $('#in-min').text(formatBytes(res.stats.in_min));
                $('#in-avg').text(formatBytes(res.stats.in_avg));
                $('#in-max').text(formatBytes(res.stats.in_max));

                $('#out-last').text(formatBytes(res.stats.out_last));
                $('#out-min').text(formatBytes(res.stats.out_min));
                $('#out-avg').text(formatBytes(res.stats.out_avg));
                $('#out-max').text(formatBytes(res.stats.out_max));
            }
        }
    });
}

// 3. Gabungkan Timer: Update Stats Atas & Update Grafik Interface
setInterval(function() {
    updateDashboardStats(); // Fungsi yang sudah ada di atas
    updateInterfaceChart();  // Fungsi baru ini
}, 5000);

// 4. Event Handler Dropdown
$('#select-interface, #select-time-range').on('change', function() {
    updateInterfaceChart();
});

// 5. Fungsi Bantu Format Bytes (Taruh paling bawah)
function formatBytes(bytes, decimals = 2) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

// 6. Jalankan saat Ready
$(document).ready(function() {
    updateDashboardStats();
    
    // Auto-select interface pertama
    const firstInterface = $('#select-interface option:eq(1)').val();
    if (firstInterface) {
        $('#select-interface').val(firstInterface);
        updateInterfaceChart();
    }
});
</script>



@endsection

