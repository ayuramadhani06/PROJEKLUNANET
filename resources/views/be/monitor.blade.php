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

        {{-- BUTTON FILTER --}}
        <button class="btn btn-sm bg-gradient-info"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#columnFilter">
          Filter / Select Column
        </button>
      </div>

      {{-- ================= CHECKBOX FILTER ================= --}}
      <div class="collapse" id="columnFilter">
        <div class="card-body pt-3">
          <div class="row">
            @foreach($allColumns as $col)
              <div class="col-md-3 col-sm-6">
                <div class="form-check form-switch form-check-info mb-2">
                    <input class="form-check-input column-toggle"
                            type="checkbox"
                            value="{{ $col }}"
                            id="chk-{{ $col }}">
                    <label class="form-check-label text-sm text-dark"
                            for="chk-{{ $col }}">
                        {{ strtoupper(str_replace('_',' ', $col)) }}
                    </label>
                </div>
              </div>
            @endforeach
          </div>
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
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder d-none optional-col"
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
                    {{ $flow[$col] ?? '-' }}
                  </td>
                @endforeach

                {{-- OPTIONAL --}}
                @foreach($allColumns as $col)
                  <td class="text-sm d-none optional-col"
                      data-col="{{ $col }}">
                    {{ $flow[$col] ?? '-' }}
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
  document.querySelectorAll('.column-toggle').forEach(cb => {
    cb.addEventListener('change', function () {
      const col = this.value;

      document
        .querySelectorAll(`[data-col="${col}"]`)
        .forEach(el => {
          el.classList.toggle('d-none', !this.checked);
        });
    });
  });
</script>
@endsection
