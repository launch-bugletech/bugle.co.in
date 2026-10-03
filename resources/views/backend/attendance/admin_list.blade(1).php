@extends(backendView('layouts.app'))
@section('title', 'Attendance Admin List')
@php
    $pagename = $pagename ?? 'attendance';
@endphp
@section('content')
    <div class="container-fluid px-4 py-4">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1 fw-bold text-black">Attendance Records</h2>
                <p class="text-muted mb-0">Monitor and manage employee attendance</p>
            </div>
        </div>

        {{-- Export Buttons --}}
        <div class="d-flex justify-content-end mb-3">
    <a href="{{ route('attendance.export.pdf') }}" id="exportPdf" class="btn btn-danger me-2">
        <i class="bx bx-file"></i> Export PDF
    </a>
    <a href="{{ route('attendance.export.excel') }}" id="exportExcel" class="btn btn-success">
        <i class="bx bx-table"></i> Export Excel
    </a>
</div>
        {{-- Filters --}}
        <div class="card mb-3 border-0 shadow-sm">
            <div class="card-body">
                <div class="row g-3 align-items-end">

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Filter by Date</label>
                        <input type="date" id="filterDate" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Filter by Month</label>
                        <input type="month" id="filterMonth" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Filter by Employee</label>
                        <input type="text" id="filterEmployee" class="form-control" placeholder="Enter employee name">
                    </div>

                    <div class="col-md-2">
                        <button id="resetFilters" class="btn btn-dark w-100">
                            Reset
                        </button>
                    </div>

                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="card border-0 shadow-sm bg-white">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="attendanceDataTable" class="table table-hover align-middle mb-0 w-100">
                        <thead class="bg-light">
                            <tr>
                                <th>Employee</th>
                                <th>Clock In Address</th>
                                <th>Clock Out Address</th>
                                <th class="text-center">Duration</th>
                                <th class="text-center">IP Address</th>
                                <th class="text-center">In Date Time</th>
                                <th class="text-center">Out Date Time</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($attendances as $a)
                                <tr data-clock-date="{{ \Carbon\Carbon::parse($a->clock_in_at)->format('Y-m-d') }}">

                                    {{-- Employee --}}
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle border border-dark d-flex align-items-center justify-content-center me-3"
                                                style="width:40px;height:40px;">
                                                <span class="fw-bold">
                                                    {{ substr($a->user->name ?? 'U', 0, 1) }}
                                                </span>
                                            </div>
                                            <div>
                                                <p class="mb-0 fw-semibold">
                                                    <a href="{{ route('attendance.employee.monthly', $a->user->id) }}"
                                                        class="text-decoration-none text-dark">
                                                        {{ $a->user->first_name ?? '' }} {{ $a->user->last_name ?? '' }}
                                                    </a>
                                                </p>

                                            </div>
                                        </div>
                                    </td>

                                    {{-- Clock In --}}
<td>
    @if($a->clock_in_lat && $a->clock_in_lng)
        <span class="address-cell text-wrap"
              data-lat="{{ $a->clock_in_lat }}"
              data-lng="{{ $a->clock_in_lng }}">
            <small class="text-muted">Loading...</small>
        </span>
    @else
        <span class="text-muted">N/A</span>
    @endif
</td>

{{-- Clock Out --}}
<td>
    @if($a->clock_out_lat && $a->clock_out_lng)
        <span class="address-cell text-wrap"
              data-lat="{{ $a->clock_out_lat }}"
              data-lng="{{ $a->clock_out_lng }}">
            <small class="text-muted">Loading...</small>
        </span>
    @else
        <span class="badge bg-dark">Active</span>
    @endif
</td>

                                    {{-- Duration --}}
                                    <td class="text-center">
                                        @if($a->clock_out_at)
                                            @php
                                                $d = \Carbon\Carbon::parse($a->clock_in_at)
                                                    ->diff(\Carbon\Carbon::parse($a->clock_out_at));
                                                $hours = ($d->days * 24) + $d->h;
                                            @endphp
                                            <span class="badge bg-light text-dark border">
                                                {{ $hours }}h {{ $d->i }}m
                                            </span>
                                        @else
                                            —
                                        @endif
                                    </td>

                                    {{-- IP --}}
                                    <td class="text-center">
                                        <span class="badge bg-white text-dark border">
                                            IN: {{ $a->clock_in_ip ?? 'N/A' }}
                                        </span><br>
                                        <span class="badge bg-white text-dark border mt-1">
                                            OUT: {{ $a->clock_out_ip ?? 'N/A' }}
                                        </span>
                                    </td>

                                    {{-- IN Date Time --}}
                                    <td class="text-center">
                                        {{ \Carbon\Carbon::parse($a->clock_in_at)->format('d M Y, h:i A') }}
                                    </td>

                                    {{-- OUT Date Time --}}
                                    <td class="text-center">
                                        @if($a->clock_out_at)
                                            {{ \Carbon\Carbon::parse($a->clock_out_at)->format('d M Y, h:i A') }}
                                        @else
                                            <span class="badge bg-dark">Active</span>
                                        @endif
                                    </td>

                                </tr>

                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        No attendance records found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
@endsection

{{-- Styles --}}
@push('styles')
    <link rel="stylesheet" href="{!! backendAssets('dist/assets/plugin/datatables/dataTables.bootstrap5.min.css') !!}">
    <style>
        .text-wrap {
            white-space: normal !important;
            word-break: break-word;
            max-width: 280px;
        }
    </style>
@endpush

{{-- Scripts --}}
@push('scripts')
    <script src="{!! backendAssets('dist/assets/bundles/dataTables.bundle.js') !!}"></script>
@endpush

{{-- DataTable + Filters --}}
@push('custom_scripts')
    <script>
        $(document).ready(function () {

            let table = $('#attendanceDataTable').DataTable({
                responsive: true,
                autoWidth: false,
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                order: [[5, 'desc']]
            });

            // Employee filter
            $('#filterEmployee').on('keyup change', function () {
                table.column(0).search(this.value).draw();
            });

            // Date & Month filter
            $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {

                let selectedDate = $('#filterDate').val();
                let selectedMonth = $('#filterMonth').val();

                let rowNode = table.row(dataIndex).node();
                if (!rowNode) return true;

                let rowDate = rowNode.getAttribute('data-clock-date');
                if (!rowDate) return true;

                if (selectedDate && rowDate !== selectedDate) return false;
                if (selectedMonth && rowDate.substring(0, 7) !== selectedMonth) return false;

                return true;
            });

            $('#filterDate, #filterMonth').on('change', function () {
                table.draw();
            });

            $('#resetFilters').on('click', function () {
                $('#filterDate,#filterMonth,#filterEmployee').val('');
                table.search('').columns().search('').draw();
            });

        });
    </script>
    
    <script>
$(document).ready(function () {

    function buildExportUrl(baseUrl) {
        let date = $('#filterDate').val();
        let month = $('#filterMonth').val();
        let employee = $('#filterEmployee').val();

        let params = [];

        if (date) params.push('date=' + date);
        if (month) params.push('month=' + month);
        if (employee) params.push('employee=' + encodeURIComponent(employee));

        return baseUrl + (params.length ? '?' + params.join('&') : '');
    }

    $('#exportPdf').click(function (e) {
        e.preventDefault();
        window.location.href = buildExportUrl("{{ route('attendance.export.pdf') }}");
    });

    $('#exportExcel').click(function (e) {
        e.preventDefault();
        window.location.href = buildExportUrl("{{ route('attendance.export.excel') }}");
    });

});


// Fetch addresses via AJAX — same URL, no extra route needed
const addressCells = document.querySelectorAll('.address-cell');
const fetchQueue = Array.from(addressCells);

// Process one at a time to avoid hammering the geocoding API
function processNext() {
    if (fetchQueue.length === 0) return;

    const cell = fetchQueue.shift();
    const lat = cell.dataset.lat;
    const lng = cell.dataset.lng;

    fetch(`{{ url()->current() }}?lat=${lat}&lng=${lng}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        cell.innerHTML = data.address ?? 'N/A';
        processNext(); // fetch next after current is done
    })
    .catch(() => {
        cell.innerHTML = 'Unavailable';
        processNext();
    });
}

processNext(); // start the chain
</script>
@endpush