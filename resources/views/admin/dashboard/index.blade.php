@extends('layouts.admin')

@section('content')
    <div class="section-header">
        <h1>Dashboard</h1>
    </div>

    <div class="section-body">
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
                        <canvas id="chart-subscriptions" height="200"></canvas>
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
                        <canvas id="chart-revenue" height="200"></canvas>
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
