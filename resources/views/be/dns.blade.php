@extends('be.master')

@php
  $title = 'Network Traffic Monitor';
  $breadcrumb = 'DNS Insights';
@endphp

@section('content')
<div class="container-fluid py-2">
    {{-- Clean Stats Row (Tanpa Logo) --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-4 col-sm-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <p class="text-sm mb-1 text-uppercase font-weight-bold text-muted">Total Traffic</p>
                    <h3 class="font-weight-bolder mb-0" id="stat-total-query">0</h3>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-sm-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <p class="text-sm mb-1 text-uppercase font-weight-bold text-muted">Active Devices</p>
                    <h3 class="font-weight-bolder mb-0" id="stat-active-ip">0</h3>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-sm-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <p class="text-sm mb-1 text-uppercase font-weight-bold text-muted">Top Domain</p>
                    <h3 class="font-weight-bolder mb-0 text-primary text-truncate" id="stat-top-domain">-</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content Card --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <h5 class="mb-0 fw-bold">Live Activity Feed</h5>
                </div>
                <div class="col-md-8 text-end">
                    <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                        <div class="form-check form-switch d-flex align-items-center mb-0 px-0 me-3">
                            <input class="form-check-input ms-0 mt-0" type="checkbox" id="filter-dhcp" style="cursor: pointer;">
                            <label class="form-check-label ms-2 text-xs font-weight-bold text-muted mb-0" for="filter-dhcp">DHCP ONLY</label>
                        </div>
                        
                        <div class="input-group input-group-sm" style="width: 160px;">
                            <span class="input-group-text border-0 bg-light"><i class="fas fa-calendar-alt text-muted"></i></span>
                            <input type="date" id="dns-date" class="form-control border-0 bg-light text-xs" value="{{ date('Y-m-d') }}">
                        </div>

                        <div class="input-group input-group-sm shadow-none" style="width: 280px;">
                            <span class="input-group-text border-0 bg-light"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" id="dns-search" class="form-control border-0 bg-light text-xs" placeholder="Filter IP, Domain, or Status...">
                        </div>

                        <button class="btn btn-sm btn-light mb-0 shadow-none border-0" onclick="resetFilters()">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-items-center mb-0">
                    <thead>
                        <tr class="bg-light">
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-4">Timestamp & Activity</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Identity</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Network Address</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Base Domain</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody id="dns-table-body">
                        {{-- Content via JS --}}
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer bg-white border-0 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted text-xs">Showing <span id="entry-count" class="fw-bold text-dark">0</span> entries</small>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item" id="btn-prev">
                            <a class="page-link border-0 shadow-none rounded-circle mx-1" href="javascript:void(0)" onclick="changePage(-1)">
                                <i class="fas fa-chevron-left text-xs"></i>
                            </a>
                        </li>
                        <li class="d-flex align-items-center mx-2">
                            <span class="text-xs font-weight-bold">Page <span id="current-page-text">1</span> of <span id="total-page-text">1</span></span>
                        </li>
                        <li class="page-item" id="btn-next">
                            <a class="page-link border-0 shadow-none rounded-circle mx-1" href="javascript:void(0)" onclick="changePage(1)">
                                <i class="fas fa-chevron-right text-xs"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    let searchTimer;
    let currentPage = 1;

    function fetchLogs(page = 1) {
        currentPage = page;
        let searchValue = $('#dns-search').val();
        let dateValue = $('#dns-date').val();
        let onlyDhcp = $('#filter-dhcp').is(':checked');

        $.ajax({
            url: "{{ route('dns.api') }}",
            method: 'GET',
            data: { 
                search: searchValue,
                date: dateValue, 
                page: currentPage,
                only_dhcp: onlyDhcp
            },
            success: function(response) {
                $('#stat-total-query').text(response.stats.total_query.toLocaleString());
                $('#stat-active-ip').text(response.stats.active_ip);
                $('#stat-top-domain').text(response.stats.top_domain);
                $('#entry-count').text(response.total_filtered.toLocaleString());
                $('#current-page-text').text(response.current_page);
                $('#total-page-text').text(response.total_page);
                $('#btn-prev').toggleClass('disabled', response.current_page <= 1);
                $('#btn-next').toggleClass('disabled', response.current_page >= response.total_page);

                let rows = '';
                if (response.logs && response.logs.length > 0) {
                    response.logs.forEach(function(log) {
                        let statusDisplay = log.is_online 
                            ? `<span class="badge badge-sm badge-success-glow">
                                <i class="fas fa-circle anim-pulse me-1" style="font-size: 6px; vertical-align: middle;"></i> ONLINE
                               </span>` 
                            : `<span class="badge badge-sm badge-offline-gray">OFFLINE</span>`;

                        rows += `
                        <tr class="align-middle">
                            <td class="ps-4">
                                <div class="d-flex flex-column">
                                    <h6 class="mb-0 text-sm font-weight-bold">${log.log_time}</h6>
                                    <p class="text-xxs text-muted mb-0"><i class="far fa-clock me-1"></i>${log.time_ago}</p>
                                </div>
                            </td>
                            <td>
                                <span class="text-sm font-weight-bold text-dark">${log.hostname || 'Generic Device'}</span>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="text-sm fw-bold text-dark">${log.src_ip}</span>
                                    <span class="text-xxs font-monospace text-muted">${log.mac}</span>
                                </div>
                            </td>
                            <td>
                                <span class="text-sm font-weight-bold text-primary">${log.base_domain}</span>
                            </td>
                            <td class="text-center">
                                ${statusDisplay}
                            </td>
                        </tr>`;
                    });
                } else {
                    rows = '<tr><td colspan="5" class="text-center py-5 text-muted">No activities found...</td></tr>';
                }
                $('#dns-table-body').html(rows);
            }
        });
    }

    function changePage(step) {
        fetchLogs(currentPage + step);
    }

    function resetFilters() {
        $('#dns-search').val('');
        $('#dns-date').val("{{ date('Y-m-d') }}");
        $('#filter-dhcp').prop('checked', false);
        fetchLogs(1);
    }

    $('#filter-dhcp, #dns-date').on('change', () => fetchLogs(1));
    $('#dns-search').on('keyup', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => fetchLogs(1), 500);
    });

    setInterval(() => {
        if ($('#dns-date').val() === "{{ date('Y-m-d') }}" && $('#dns-search').val() === '' && !$('#dns-search').is(':focus')) {
            fetchLogs(currentPage);
        }
    }, 5000);

    $(document).ready(() => fetchLogs());
</script>

<style>
    .badge-success-glow {
        background: rgba(45, 206, 137, 0.15);
        color: #2dce89;
        border: 1px solid rgba(45, 206, 137, 0.3);
        font-weight: 700;
        letter-spacing: 0.5px;
    }
    .badge-offline-gray {
        background: #f4f5f7;
        color: #8898aa;
        border: 1px solid #e9ecef;
        font-weight: 700;
    }
    .text-xxs { font-size: 0.65rem !important; }
    
    .anim-pulse {
        animation: pulse-green 2s infinite;
    }
    @keyframes pulse-green {
        0% { transform: scale(0.95); opacity: 1; }
        50% { transform: scale(1.4); opacity: 0.5; }
        100% { transform: scale(0.95); opacity: 1; }
    }

    tbody tr { transition: all 0.2s ease; }
    tbody tr:hover { background-color: rgba(94, 114, 228, 0.02); }
    .page-link:hover { background-color: #5e72e4; color: white !important; }
</style>
@endsection