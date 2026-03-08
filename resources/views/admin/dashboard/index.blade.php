@extends('layouts.admin')

@section('content')
    <div class="section-header">
        <h1>Dashboard</h1>
        <div class="section-header-button">
            <button type="button" class="btn btn-outline-primary btn-sm" data-toggle="collapse" data-target="#dashboard-date-filter"
                aria-expanded="{{ ($stats['filter_start'] ?? null) ? 'true' : 'false' }}" aria-controls="dashboard-date-filter">
                <i class="fas fa-calendar-alt mr-1"></i> Date filter
            </button>
        </div>
    </div>

    <div class="section-body">
        <div class="card collapse mb-3 {{ ($stats['filter_start'] ?? null) ? 'show' : '' }}" id="dashboard-date-filter">
            <div class="card-body py-3">
                <form method="get" action="{{ route('admin.dashboard') }}" class="dashboard-filter-form" id="dashboard-date-form">
                    <div class="row align-items-end flex-wrap">
                        <div class="col-md-3 col-6 mb-2 mb-md-0">
                            <label for="start_date" class="form-label mb-1 small font-weight-bold">Start date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control form-control-sm"
                                value="{{ $stats['filter_start'] ?? '' }}" max="{{ $stats['filter_end'] ?? date('Y-m-d') }}">
                        </div>
                        <div class="col-md-3 col-6 mb-2 mb-md-0">
                            <label for="end_date" class="form-label mb-1 small font-weight-bold">End date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control form-control-sm"
                                value="{{ $stats['filter_end'] ?? '' }}" min="{{ $stats['filter_start'] ?? '' }}" max="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-4 col-12 mt-2 mt-md-0">
                            <button type="submit" class="btn btn-primary btn-sm mr-1"><i class="fas fa-filter mr-1"></i> Apply</button>
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
                        </div>
                    </div>
                </form>
                @if($stats['filter_start'] ?? null)
                    <p class="small text-muted mb-0 mt-2">Showing data from <strong>{{ \Carbon\Carbon::parse($stats['filter_start'])->format('d M Y') }}</strong> to <strong>{{ \Carbon\Carbon::parse($stats['filter_end'])->format('d M Y') }}</strong>. <a href="{{ route('admin.dashboard') }}">Show all time</a>.</p>
                @endif
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var start = document.getElementById('start_date');
                var end = document.getElementById('end_date');
                if (start && end) {
                    start.addEventListener('change', function() { end.min = start.value || ''; });
                    end.addEventListener('change', function() { start.max = end.value || ''; });
                }
            });
        </script>

        <div class="row">
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Total Users</h4>
                        </div>
                        <div class="card-body">{{ $stats['total_users'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-success">
                        <i class="fas fa-crown"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Active Subscriptions</h4>
                        </div>
                        <div class="card-body">{{ $stats['active_subscriptions'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-warning">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Total Revenue</h4>
                        </div>
                        <div class="card-body">₹{{ number_format($stats['total_revenue'] ?? 0, 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-danger">
                        <i class="fas fa-cog"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Payment Gateways</h4>
                        </div>
                        <div class="card-body">{{ $stats['total_gateways'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Plan-wise stats and charts --}}
        <div class="row mt-4">
            <div class="col-lg-6 col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Users & subscriptions by plan</h4>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Plan</th>
                                        <th class="text-center">Active subscriptions</th>
                                        <th class="text-center">Total purchases</th>
                                        <th class="text-right">Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($stats['plan_stats'] ?? [] as $row)
                                    <tr>
                                        <td><strong>{{ $row['name'] }}</strong></td>
                                        <td class="text-center">{{ $row['active_subscriptions'] }}</td>
                                        <td class="text-center">{{ $row['total_purchases'] }}</td>
                                        <td class="text-right">₹{{ number_format($row['revenue'], 2) }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="4" class="text-center text-muted">No plans yet</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Active subscriptions by plan</h4>
                    </div>
                    <div class="card-body">
                        <div style="position:relative; min-height:220px;">
                            <canvas id="chart-subscriptions"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-lg-6 col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Revenue by plan</h4>
                    </div>
                    <div class="card-body">
                        <div style="position:relative; min-height:220px;">
                            <canvas id="chart-revenue"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('js/Chart.min.js') }}"></script>
<script>
(function () {
    var labels = @json($stats['chart_labels'] ?? []);
    var subscriptions = @json($stats['chart_subscriptions'] ?? []);
    var revenue = @json($stats['chart_revenue'] ?? []);

    var colors = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796'];

    if (labels.length && (subscriptions.some(function (n) { return n > 0; }) || revenue.some(function (n) { return n > 0; }))) {
        if (subscriptions.some(function (n) { return n > 0; })) {
            new Chart(document.getElementById('chart-subscriptions'), {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: subscriptions,
                        backgroundColor: colors.slice(0, labels.length),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: { position: 'bottom' }
                }
            });
        }
        if (revenue.some(function (n) { return n > 0; })) {
            new Chart(document.getElementById('chart-revenue'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Revenue (₹)',
                        data: revenue,
                        backgroundColor: 'rgba(78, 115, 223, 0.7)',
                        borderColor: '#4e73df',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: { display: false },
                    scales: {
                        yAxes: [{ ticks: { beginAtZero: true } }]
                    }
                }
            });
        }
    }
})();
</script>
@endpush
