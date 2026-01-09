@extends('be.master')

@php
  $title = 'Monitoring Traffic Flow';
  $breadcrumb = 'Monitoring';
@endphp

@section('content')

<style>
  /* Efek Meredup pada Tabel saat proses refresh */
  .table-loading {
      opacity: 0.4; /* Opacity redupnya */
      pointer-events: none;
      transition: opacity 0.3s ease-in-out;
  }

  .header-controls .form-control, 
  .header-controls .btn {
      margin-bottom: 0 !important;
  }
</style>

{{-- ================= HEADER + FILTER BUTTON ================= --}}
<div class="row mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-header pb-4 d-flex justify-content-between align-items-center">
        <div>
          <h6>Traffic Flow Monitoring</h6>
          <p class="text-sm text-secondary mb-0">
            Select columns to display NetFlow details
          </p>
        </div>

        {{-- Grouping utama dengan gap-3 yang konsisten --}}
        <div class="d-flex align-items-center gap-3 header-controls">
          
          {{-- SELECT JUMLAH BARIS --}}
          <form method="GET" class="d-flex align-items-center gap-2">
            <span class="text-xs text-secondary text-nowrap">Rows</span>
            <input type="hidden" name="search" value="{{ request('search') }}">
            @foreach((array) request('columns', []) as $col)
              <input type="hidden" name="columns[]" value="{{ $col }}">
            @endforeach
            <input type="number" name="per_page" value="{{ $perPage }}" min="1" max="100" 
                   class="form-control form-control-sm" style="width: 70px;" onchange="this.form.submit()">
          </form>

          {{-- BUTTON FILTER --}}
          <button class="btn btn-sm bg-gradient-info text-nowrap" type="button" data-bs-toggle="collapse" data-bs-target="#columnFilter" style="background: linear-gradient(135deg, #8b0000 0%, #4a0e4e 100%);">
            Filter Column
          </button>

          {{-- SELECT AUTO REFRESH (TANPA ICON LOADING) --}}
          <div class="d-flex align-items-center">
            <select class="form-select form-select-sm" id="refresh-interval" style="width: 130px;">
                <option value="0">Refresh: Off</option>
                <option value="10000">10 Seconds</option>
                <option value="30000">30 Seconds</option>
                <option value="60000">1 Minute</option>
                <option value="300000" selected>5 Minutes</option>
                <option value="600000">10 Minutes</option>
                <option value="1800000">30 Minutes</option>

                <option value="3600000">1 Hour</option>
                <option value="7200000">2 Hour</option>
                <option value="86400000">1 Day</option>
            </select>
          </div>

        </div>
      </div>

      {{-- ================= CHECKBOX FILTER ================= --}}
      <div class="collapse {{ request('open_filter') ? 'show' : '' }}" id="columnFilter">
        <div class="card-body pt-3">
          <form method="GET" action="{{ route('traffic.index') }}" id="columnForm">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <input type="hidden" name="per_page" value="{{ request('per_page', $perPage) }}">
            <input type="hidden" name="open_filter" value="1">
            <div class="row">
              @foreach($allColumns as $col)
                <div class="col-md-3 col-sm-6">
                  <div class="form-check form-switch form-check-info mb-2">
                    <input class="form-check-input column-toggle" type="checkbox" name="columns[]" value="{{ $col }}" 
                           id="chk-{{ $col }}" {{ in_array($col, $selectedColumns ?? []) ? 'checked' : '' }}>
                    <label class="form-check-label text-sm text-dark" for="chk-{{ $col }}">
                      {{ strtoupper(str_replace('_',' ', $col)) }}
                    </label>
                  </div>
                </div>
              @endforeach
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ================= TABLE ================= --}}
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body px-0 pt-0 pb-2">
        <div class="table-responsive p-0" id="table-container">
          <table class="table table-sm align-items-center mb-0">
            <thead>
              <tr>
                @foreach($defaultColumns as $col)
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder">
                    {{ strtoupper(str_replace('_',' ', $col)) }}
                  </th>
                @endforeach
                @foreach($allColumns as $col)
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder optional-col
                      {{ in_array($col, $selectedColumns ?? []) ? '' : 'd-none' }}" data-col="{{ $col }}">
                    {{ strtoupper(str_replace('_',' ', $col)) }}
                  </th>
                @endforeach
              </tr>
            </thead>
            <tbody>
            @foreach($flows as $flow)
              <tr>
                @foreach($defaultColumns as $col)
                  <td class="text-sm font-monospace">{{ $flow->$col ?? '-' }}</td>
                @endforeach
                @foreach($allColumns as $col)
                  <td class="text-sm optional-col {{ in_array($col, $selectedColumns ?? []) ? '' : 'd-none' }}" data-col="{{ $col }}">
                    {{ $flow->$col ?? '-' }}
                  </td>
                @endforeach
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
  document.addEventListener('DOMContentLoaded', function() {
    
    let refreshInterval;
    const intervalSelect = document.getElementById('refresh-interval');
    const tableContainer = document.getElementById('table-container');

    function startAutoRefresh(interval) {
        if (refreshInterval) clearInterval(refreshInterval);
        if (interval == 0) return;
        refreshInterval = setInterval(() => { fetchTableData(); }, interval);
    }

    function fetchTableData() {
        // Hanya efek meredupkan tabel
        tableContainer.classList.add('table-loading');

        fetch(window.location.href)
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newTableContent = doc.getElementById('table-container').innerHTML;
                tableContainer.innerHTML = newTableContent;
            })
            .catch(error => console.error('Error:', error))
            .finally(() => {
                // Menghilangkan efek redup setelah selesai
                setTimeout(() => {
                    tableContainer.classList.remove('table-loading');
                }, 400);
            });
    }

    intervalSelect.addEventListener('change', function() {
        startAutoRefresh(this.value);
    });

    // Jalankan auto refresh sesuai pilihan awal
    startAutoRefresh(intervalSelect.value);

    // Filter Column Toggle
    document.querySelectorAll('.column-toggle').forEach(cb => {
      cb.addEventListener('change', function () {
        let form = document.getElementById('columnForm');
        let flag = document.createElement('input');
        flag.type = 'hidden'; flag.name = 'open_filter'; flag.value = '1';
        form.appendChild(flag);
        form.submit();
      });
    });
  });
</script>
@endsection