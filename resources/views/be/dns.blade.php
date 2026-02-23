@extends('be.master')

@php
  $title = 'DNS Logging';
  $breadcrumb = 'DNS Logging';
@endphp

@section('content')
{{-- Stats Row --}}
<div class="row mb-3">
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body py-3">
                <small class="text-muted">Total Traffic Hits</small>
                <h5 class="mb-0 fw-bold" id="stat-total-query">0</h5>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body py-3">
                <small class="text-muted">Active Devices</small>
                <h5 class="mb-0 fw-bold" id="stat-active-ip">0</h5>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body py-3">
                <small class="text-muted">Top Interest</small>
                <h5 class="mb-0 fw-bold text-primary" id="stat-top-domain">-</h5>
            </div>
        </div>
    </div>
</div>

{{-- DNS Table --}}
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <h6 class="mb-2 mb-md-0">Live DNS Enrichment Feed</h6>
        
        <div class="d-flex gap-2">
            {{-- Filter Tanggal --}}
            <div class="input-group input-group-sm" style="width: 170px;">
                <span class="input-group-text bg-light border-end-0"><i class="fas fa-calendar-alt text-muted"></i></span>
                <input type="date" id="dns-date" class="form-control bg-light border-start-0" value="{{ date('Y-m-d') }}">
            </div>

            {{-- Search Box --}}
            <div class="input-group input-group-sm" style="width: 250px;">
                <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                <input type="text" id="dns-search" class="form-control bg-light border-start-0" placeholder="Search IP, Domain, Host...">
            </div>
            
            {{-- Tombol Reset --}}
            <button class="btn btn-sm btn-outline-secondary mb-0" onclick="resetFilters()" title="Reset Filter">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>
    
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-items-center mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="text-xs">Time</th>
                        <th class="text-xs">Device / Hostname</th>
                        <th class="text-xs">Source IP</th>
                        <th class="text-xs">MAC Address</th>
                        <th class="text-xs">Base Domain</th>
                        <th class="text-xs text-center">Hits</th>
                    </tr>
                </thead>
                <tbody id="dns-table-body" class="text-sm">
                    {{-- Data diisi via JS --}}
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-0 py-2">
            <small class="text-muted">
                Total entries in current view: <span id="entry-count" class="fw-bold text-dark">0</span>
            </small>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    let searchTimer;

    function fetchLogs() {
        let searchValue = $('#dns-search').val();
        let dateValue = $('#dns-date').val();

        $.ajax({
            url: "{{ route('dns.api') }}",
            method: 'GET',
            data: { 
                search: searchValue,
                date: dateValue 
            },
            success: function(response) {
                // Update Stats
                $('#stat-total-query').text(response.stats.total_query.toLocaleString());
                $('#stat-active-ip').text(response.stats.active_ip);
                $('#stat-top-domain').text(response.stats.top_domain);
                $('#entry-count').text(response.total_all.toLocaleString());

                // Update Table
                let rows = '';
                if (response.logs.length > 0) {
                    response.logs.forEach(function(log) {
                        let domainClass = log.base_domain === 'Infrastructure/Resources' ? 'text-muted italic' : 'fw-bold text-dark';
                        let hostname = log.hostname ? log.hostname : '<span class="text-muted">Unknown</span>';
                        
                        rows += `
                        <tr>
                            <td class="text-muted">${log.log_time}</td>
                            <td>${hostname}</td>
                            <td><span class="badge bg-light text-dark border">${log.src_ip}</span></td>
                            <td class="text-xs font-monospace text-uppercase">${log.mac}</td>
                            <td class="${domainClass}">${log.base_domain}</td>
                            <td class="text-center"><span class="badge bg-info">${log.total_hits}</span></td>
                        </tr>`;
                    });
                } else {
                    rows = '<tr><td colspan="6" class="text-center py-5 text-muted">No data found for the selected criteria...</td></tr>';
                }
                $('#dns-table-body').html(rows);
            },
            error: function() {
                console.error("Failed to fetch DNS logs.");
            }
        });
    }

    // Reset Filter ke Hari Ini
    function resetFilters() {
        $('#dns-search').val('');
        $('#dns-date').val("{{ date('Y-m-d') }}");
        fetchLogs();
    }

    // Debounce Search (Menunggu user selesai mengetik)
    $('#dns-search').on('keyup', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(fetchLogs, 500);
    });

    // Detect perubahan tanggal
    $('#dns-date').on('change', function() {
        fetchLogs();
    });

    // Auto Refresh setiap 3 detik
    // Catatan: Refresh otomatis berhenti jika user sedang fokus mencari atau melihat tanggal kemarin
    setInterval(function() {
        let isToday = $('#dns-date').val() === "{{ date('Y-m-d') }}";
        let isNotSearching = $('#dns-search').val() === '';
        
        if (isToday && isNotSearching && !$('#dns-search').is(':focus')) {
            fetchLogs();
        }
    }, 3000);

    // Initial Load
    $(document).ready(function() {
        fetchLogs();
    });
</script>

<style>
    .italic { font-style: italic; }
    .font-monospace { font-family: 'Courier New', Courier, monospace; }
</style>
@endsection