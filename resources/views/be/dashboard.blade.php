@extends('be.master')

@php
  $title = 'Dashboard';
  $breadcrumb = 'Dashboard';
@endphp

@section('content')

{{-- ================= CARD STATISTIK ================= --}}
<div class="row">
  <div class="col-xl-3 col-sm-6 mb-4">
    <div class="card">
      <div class="card-body p-3">
        <div class="row">
          <div class="col-8">
            <p class="text-sm mb-0 text-capitalize font-weight-bold">
              Total Traffic
            </p>
            <h5 class="font-weight-bolder mb-0">
              {{ number_format($totalTraffic) }} bytes
            </h5>
          </div>
          <div class="col-4 text-end">
            <div class="icon bg-gradient-primary shadow text-center border-radius-md">
              <i class="ni ni-chart-bar-32 text-lg opacity-10"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ================= CHART ================= --}}
<div class="row mt-4">
  <div class="col-12">
    <div class="card">
      <div class="card-header pb-0">
        <h6>Top Destination Traffic</h6>
      </div>
      <div class="card-body">
        <canvas id="chart-bars" height="120"></canvas>
      </div>
    </div>
  </div>
</div>

{{-- ================= TABLE TOP IP ================= --}}
<div class="row mt-4">
  <div class="col-lg-6 mb-lg-0 mb-4">
    <div class="card">
      <div class="card-header pb-0">
        <h6>Top IP by Traffic</h6>
      </div>
      <div class="card-body pt-0 pb-2">
        <div class="table-responsive">
          <table class="table align-items-center mb-0">
            <thead>
              <tr>
                <th class="text-uppercase text-secondary text-xs font-weight-bolder text-start ps-4">
                  IP Address
                </th>
                <th class="text-uppercase text-secondary text-xs font-weight-bolder text-end pe-4">
                  Total Bytes
                </th>
              </tr>
            </thead>
            <tbody>
              @foreach($topIps as $ip)
              <tr>
                <td class="text-sm text-dark ps-4">
                  {{ $ip['ip'] }}
                </td>
                <td class="text-sm text-end fw-bold pe-4">
                  {{ number_format($ip['bytes']) }}
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

{{-- ================= LIVE TRAFFIC TABLE ================= --}}
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header pb-0">
        <h6>Live Traffic Monitoring</h6>
      </div>
      <div class="card-body pt-0 pb-2">
        <div class="table-responsive">
          <table class="table align-items-center mb-0">
            <thead>
              <tr>
                <th class="text-uppercase text-secondary text-xs font-weight-bolder text-start ps-4">
                  Src IP
                </th>
                <th class="text-uppercase text-secondary text-xs font-weight-bolder text-start">
                  Dst IP
                </th>
                <th class="text-uppercase text-secondary text-xs font-weight-bolder text-center">
                  Packets
                </th>
                <th class="text-uppercase text-secondary text-xs font-weight-bolder text-center pe-4">
                  Dst Hostname
                </th>
              </tr>
            </thead>
            <tbody>
              @foreach($liveFlows as $flow)
              <tr>
                <td class="text-sm text-dark ps-4">
                  {{ $flow['src_ip'] }}
                </td>
                <td class="text-sm text-dark">
                  {{ $flow['dst_ip'] }}
                </td>
                <td class="text-sm fw-bold text-center">
                  {{ $flow['packets'] }}
                </td>
                <td class="text-sm text-secondary text-center pe-4">
                  {{ $flow['dst_hostname'] ?? '-' }}
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('script')
<script>
  const labels = {!! json_encode($topDest->pluck('destination')) !!};
  const traffic = {!! json_encode($topDest->pluck('total_bytes')) !!};

  const ctx = document.getElementById("chart-bars").getContext("2d");

  new Chart(ctx, {
    type: "bar",
    data: {
      labels: labels,
      datasets: [{
        label: "Traffic (bytes)",
        data: traffic,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
    }
  });
</script>
@endsection
