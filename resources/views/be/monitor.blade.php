@extends('be.master')

@php
  $title = 'Monitoring Traffic Flow';
  $breadcrumb = 'Monitoring';
@endphp

@section('content')

{{-- ================= HEADER + FILTER BUTTON ================= --}}
<div class="row mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-header pb-0 d-flex justify-content-between align-items-center">
        <div>
          <h6>Traffic Flow Monitoring</h6>
          <p class="text-sm text-secondary mb-0">
            Select columns to display NetFlow details
          </p>
        </div>

        <div class="d-flex align-items-center gap-3">
          {{-- SELECT JUMLAH BARIS --}}
          <form method="GET" class="d-flex align-items-center gap-2 mb-3">
            <span class="text-xs text-secondary">Rows</span>

            <input type="hidden" name="search" value="{{ request('search') }}">

            @foreach((array) request('columns', []) as $col)
              <input type="hidden" name="columns[]" value="{{ $col }}">
            @endforeach

            @if(request('open_filter'))
              <input type="hidden" name="open_filter" value="1">
            @endif

            <input type="number"
                  name="per_page"
                  value="{{ $perPage }}"
                  min="1"
                  max="500"
                  class="form-control form-control-sm"
                  style="width: 90px"
                  onchange="this.form.submit()">
          </form>


          {{-- BUTTON FILTER --}}
          <button class="btn btn-sm bg-gradient-info"
                  type="button"
                  data-bs-toggle="collapse"
                  data-bs-target="#columnFilter">
            Filter / Select Column
          </button>

          {{-- INFO AUTO REFRESH --}}
          <div class="d-flex align-items-center gap-2 mb-3">
            <span class="badge bg-success rounded-circle"
                  style="width:8px;height:8px;padding:0;"></span>
            <span class="text-xs text-secondary">
              Auto refresh active (5 min)
            </span>
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
                    <input
                      class="form-check-input column-toggle"
                      type="checkbox"
                      name="columns[]"
                      value="{{ $col }}"
                      id="chk-{{ $col }}"
                      {{ in_array($col, $selectedColumns ?? []) ? 'checked' : '' }}
                    >
                    <label class="form-check-label text-sm text-dark"
                          for="chk-{{ $col }}">
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
        <div class="table-responsive p-0">

          <table class="table table-sm align-items-center mb-0">
            <thead>
              <tr>
                {{-- DEFAULT COLUMN --}}
                @foreach($defaultColumns as $col)
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder">
                    {{ strtoupper(str_replace('_',' ', $col)) }}
                  </th>
                @endforeach

                {{-- OPTIONAL COLUMN --}}
                @foreach($allColumns as $col)
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder optional-col
                      {{ in_array($col, $selectedColumns ?? []) ? '' : 'd-none' }}"
                      data-col="{{ $col }}">
                    {{ strtoupper(str_replace('_',' ', $col)) }}
                  </th>
                @endforeach
              </tr>
            </thead>

            <tbody>
            @foreach($flows as $flow)
            <tr>

              {{-- DEFAULT --}}
              @foreach($defaultColumns as $col)
                <td class="text-sm font-monospace">
                  {{ $flow->$col ?? '-' }}
                </td>
              @endforeach

              {{-- OPTIONAL --}}
              @foreach($allColumns as $col)
                <td class="text-sm optional-col
                    {{ in_array($col, $selectedColumns ?? []) ? '' : 'd-none' }}"
                    data-col="{{ $col }}">
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
  // AUTO REFRESH (5 MENIT)
  setTimeout(function () {
      window.location.reload();
  }, 300000); // 5 menit


  //COLUMN TOGGLE
   document.querySelectorAll('.column-toggle').forEach(cb => {
    cb.addEventListener('change', function () {

      // tambahkan flag agar collapse tetap kebuka
      let form = document.getElementById('columnForm');

      let flag = document.createElement('input');
      flag.type = 'hidden';
      flag.name = 'open_filter';
      flag.value = '1';
      form.appendChild(flag);

      form.submit();
    });
  }); 
</script>
@endsection
