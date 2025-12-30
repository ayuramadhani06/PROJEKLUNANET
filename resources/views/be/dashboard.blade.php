@extends('be.master')

@php
  $title = 'Dashboard Traffic';
  $breadcrumb = 'Dashboard';
@endphp

@section('content')

{{-- ================= HEADER ================= --}}
<div class="row mb-4">
  <div class="col-12">
    <div class="card text-white border-0 shadow-lg" style="background: linear-gradient(135deg, #8b0000 0%, #4a0e4e 100%); border-radius: 15px;">
      <div class="card-body d-flex justify-content-between align-items-center p-4">
        <div class="d-flex align-items-center">
          
          <div>
            <h4 class="mb-1 fw-bold text-white">Dashboard</h4>
            <p class="text-sm mb-0 opacity-8" style="font-weight: 300;">
              Advanced NetFlow monitoring & traffic visibility
            </p>
          </div>
        </div>
        
        <div class="d-flex gap-4 text-center">
          <div class="px-3">
            <h5 class="mb-0 fw-bold text-white">{{ $activeIps ?? 0 }}</h5>
            <span class="text-xs opacity-7">Active IPs</span>
          </div>
          <div class="px-3 border-start border-white border-opacity-10">
            <h5 class="mb-0 fw-bold text-white">{{ number_format($totalPackets ?? 0) }}</h5>
            <span class="text-xs opacity-7">Packets</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ================= STAT CARDS ================= --}}
<div class="row mb-3">
  <div class="col-xl-3 col-sm-6 mb-3">
    <div class="card">
      <div class="card-body">
        <p class="text-sm mb-1">Total Traffic</p>
        <h5 class="fw-bold">{{ $totalTrafficHuman ?? '0 MB' }}</h5>
      </div>
    </div>
  </div>

  <div class="col-xl-3 col-sm-6 mb-3">
    <div class="card">
      <div class="card-body">
        <p class="text-sm mb-1">Packets / sec</p>
        <h5 class="fw-bold">{{ $pps ?? 0 }}</h5>
      </div>
    </div>
  </div>

  <div class="col-xl-3 col-sm-6 mb-3">
    <div class="card">
      <div class="card-body">
        <p class="text-sm mb-1">Active Flows</p>
        <h5 class="fw-bold">{{ $activeFlows ?? 0 }}</h5>
      </div>
    </div>
  </div>
</div>

{{-- ================= SUB MENU (DI BAWAH STAT) ================= --}}
<ul class="nav nav-tabs mb-4" role="tablist" >
  <li class="nav-item">
    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">
      Overview
    </button>
  </li>
  <li class="nav-item">
    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#protocols" type="button" role="tab">
      Protocols
    </button>
  </li>
</ul>


{{-- ================= TAB CONTENT ================= --}}
<div class="tab-content">

  {{-- ========== OVERVIEW ========== --}}
  <div class="tab-pane fade show active" id="overview">

    <div class="row">
      <div class="col-lg-7 mb-4">
        <div class="card h-100">
          <div class="card-header pb-0">
            <h6>Traffic Volume</h6>
          </div>
          <div class="card-body">
            <canvas id="chart-bars"></canvas>
          </div>
        </div>
      </div>

      <div class="col-lg-5">
        <div class="card h-100">
          <div class="card-header pb-0">
            <h6>Top Traffic Sources</h6>
          </div>
          <div class="card-body">
            @foreach($topIps as $ip)
              <div class="d-flex justify-content-between mb-2">
                <span class="fw-bold">{{ $ip->ip }}</span>
                <span>{{ number_format($ip->bytes) }} bytes</span>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>

  </div>

  {{-- ========== PROTOCOLS ========== --}}
  <div class="tab-pane fade" id="protocols">
    <div class="row">

      {{-- ===== DONUT + LEGEND ===== --}}
      <div class="col-lg-7 mb-3">
        <div class="card h-100">
          <div class="card-header pb-0">
            <h6>Protocol Distribution</h6>
          </div>

          <div class="card-body d-flex align-items-center">
            {{-- Donut --}}
            <div style="flex:1; min-width:220px;">
              <canvas id="protocol-donut" style="max-height:240px;"></canvas>
            </div>

            {{-- Legend --}}
            <div style="width:180px;" class="ps-3">
              <ul class="list-unstyled mb-0 small">

                <li class="mb-2 d-flex justify-content-between align-items-center">
                  <span>
                    <span class="legend-dot me-2" style="background:#3B82F6;"></span>
                    TCP
                  </span>
                  <strong>{{ number_format($tcpPackets ?? 0) }}</strong>
                </li>

                <li class="mb-2 d-flex justify-content-between align-items-center">
                  <span>
                    <span class="legend-dot me-2" style="background:#22C55E;"></span>
                    UDP
                  </span>
                  <strong>{{ number_format($udpPackets ?? 0) }}</strong>
                </li>

                <li class="d-flex justify-content-between align-items-center">
                  <span>
                    <span class="legend-dot me-2" style="background:#F59E0B;"></span>
                    ICMP
                  </span>
                  <strong>{{ number_format($icmpPackets ?? 0) }}</strong>
                </li>

              </ul>
            </div>
          </div>
        </div>
      </div>

      {{-- ===== INFO CARDS ===== --}}
      <div class="col-lg-5">
        <div class="row g-3">

          {{-- TCP --}}
          <div class="col-12">
            <div class="card p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <div class="text-xs opacity-8">TCP Traffic</div>
                  <div class="fw-bold">{{ number_format($tcpPackets ?? 0) }} packets</div>
                  <div class="text-muted small">{{ $tcpBytesHuman ?? '0 MB' }}</div>
                </div>
                <span class="badge" style="background-color: #8b0000;">TCP</span>
              </div>
            </div>
          </div>

          {{-- UDP --}}
          <div class="col-12">
            <div class="card p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <div class="text-xs opacity-8">UDP Traffic</div>
                  <div class="fw-bold">{{ number_format($udpPackets ?? 0) }} packets</div>
                  <div class="text-muted small">{{ $udpBytesHuman ?? '0 MB' }}</div>
                </div>
                <span class="badge" style="background-color: #4a0e4e;">UDP</span>
              </div>
            </div>
          </div>

          {{-- ICMP --}}
          <div class="col-12">
            <div class="card p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <div class="text-xs opacity-8">ICMP Traffic</div>
                  <div class="fw-bold">{{ number_format($icmpPackets ?? 0) }} packets</div>
                  <div class="text-muted small">{{ $icmpBytesHuman ?? '0 MB' }}</div>
                </div>
                <span class="badge" style="background-color: #506384ff;">ICMP</span>
              </div>
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
/* ================= TRAFFIC VOLUME (LINE) ================= */
const trafficCanvas = document.getElementById("chart-bars");
if (trafficCanvas) {
  const ctx = trafficCanvas.getContext("2d");
  new Chart(ctx, {
    type: "line",
    data: {
      labels: {!! json_encode($topDest->pluck('destination')) !!},
      datasets: [{
        label: "Traffic Volume",
        data: {!! json_encode($topDest->pluck('total_bytes')) !!},
        fill: true,
        tension: 0.4,
        borderWidth: 3,
        borderColor: "#8b0000",
        backgroundColor: "rgba(139, 0, 0, 0.1)",
        pointRadius: 3
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          enabled: true,
          callbacks: {
            label: function(ctx) {
              const val = ctx.raw;
              const mb = val / (1024 * 1024);
              const gb = val / (1024 * 1024 * 1024);
              return gb >= 1 ? `Traffic: ${gb.toFixed(2)} GB` : `Traffic: ${mb.toFixed(2)} MB`;
            }
          }
        }
      },
      scales: {
        x: { grid: { display: false } },
        y: {
          ticks: {
            callback: value => {
              const mb = value / (1024 * 1024);
              const gb = value / (1024 * 1024 * 1024);
              return gb >= 1 ? gb.toFixed(1) + ' GB' : mb.toFixed(0) + ' MB';
            }
          }
        }
      }
    }
  });
}

/* ================= PROTOCOL DONUT ================= */
const donutCanvas = document.getElementById("protocol-donut");
if (donutCanvas) {
  const protoLabels = {!! json_encode($protocolLabels ?? []) !!};
  const protoData   = {!! json_encode($protocolBytes ?? []) !!};

  // Warna disesuaikan biar cocok sama Maroon Dashboard
  const colorMap = {
    TCP: '#8b0000',   // Maroon
    UDP: '#4a0e4e',   // Deep Purple
    ICMP: '#506384ff'   // Dark Slate
  };

  const bgColors = protoLabels.map(p => colorMap[p] ?? '#6c757d');

  new Chart(donutCanvas.getContext("2d"), {
    type: 'doughnut',
    data: {
      labels: protoLabels,
      datasets: [{
        data: protoData,
        backgroundColor: bgColors,
        borderWidth: 0
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '65%',
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: ctx => {
              const mb = ctx.raw / (1024 * 1024);
              const gb = ctx.raw / (1024 * 1024 * 1024);
              return gb >= 1 
                ? `${ctx.label}: ${gb.toFixed(2)} GB` 
                : `${ctx.label}: ${mb.toFixed(2)} MB`;
            }
          }
        }
      }
    }
  });
}
</script>

@endsection
