@extends(backendView('layouts.app'))

@section('title', 'Monthly Attendance')

@section('content')
    <div class="container-fluid px-4 py-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold">
                    {{ $user->first_name }} {{ $user->last_name }}
                </h3>
                <p class="text-muted mb-0">Monthly Attendance</p>
            </div>

            <!-- <form method="GET" class="d-flex">
                <input type="month"
                       name="month"
                       value="{{ $month }}"
                       class="form-control me-2">
                <button class="btn btn-dark">Filter</button>
            </form> -->
        </div>


        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h6>Present Days</h6>
                        <h3 class="fw-bold text-success">{{ $summary['present'] }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h6>Absent Days</h6>
                        <h3 class="fw-bold text-danger">{{ $summary['absent'] }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h6>Total Hours</h6>
                        <h3 class="fw-bold">{{ $summary['hours'] }}</h3>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Date</th>
                                <th>Clock In</th>
                                <th>Clock Out</th>
                                <th>Duration</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attendances as $a)
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::parse($a->clock_in_at)->format('d M Y') }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($a->clock_in_at)->format('h:i A') }}</td>
                                                    <td>
                                                        {{ $a->clock_out_at
                                ? \Carbon\Carbon::parse($a->clock_out_at)->format('h:i A')
                                : 'Active'
                                                        }}
                                                    </td>
                                                    <td>
                                                        @if($a->clock_out_at)
                                                            @php
                                                                $d = \Carbon\Carbon::parse($a->clock_in_at)
                                                                    ->diff(\Carbon\Carbon::parse($a->clock_out_at));
                                                            @endphp
                                                            {{ ($d->days * 24) + $d->h }}h {{ $d->i }}m
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        No attendance found
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