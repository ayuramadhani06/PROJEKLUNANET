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
            <h5 class="mb-0 fw-bold text-white">{{ $activeIps ?? 0 }}</h5>
            <span class="text-xs opacity-7">Active IPs</span>
          </div>
          <div class="border-start ps-3">
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
  <div class="col-md-4 mb-3">
    <div class="card"><div class="card-body">
      <p class="text-sm mb-1">Total Traffic</p>
      <h5 class="fw-bold">{{ $totalTrafficHuman ?? '0 MB' }}</h5>
    </div></div>
  </div>

  <div class="col-md-4 mb-3">
    <div class="card"><div class="card-body">
      <p class="text-sm mb-1">Packets / sec</p>
      <h5 class="fw-bold">{{ $pps ?? 0 }}</h5>
    </div></div>
  </div>

  <div class="col-md-4 mb-3">
    <div class="card"><div class="card-body">
      <p class="text-sm mb-1">Active Flows</p>
      <h5 class="fw-bold">{{ $activeFlows ?? 0 }}</h5>
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
</script>
@endsection

