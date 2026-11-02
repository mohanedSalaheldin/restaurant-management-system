@extends('layouts.app')

@section('page_title', 'Financial Reports & Analytics')

@section('content')
@if ($errors->any())
    <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
@endif

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="row g-2 align-items-center">
            <div class="col-md-3">
                <label class="form-label small mb-1 fw-semibold">Start Date</label>
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1 fw-semibold">End Date</label>
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}" required>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary btn-sm me-2">
                    <i class="bi bi-filter me-1"></i> Apply Filter
                </button>
                <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card shadow-sm border-0 border-start border-success border-4 h-100 p-3">
            <span class="text-muted small fw-bold text-uppercase">Total Gross Revenue</span>
            <h3 class="fw-bold text-success mb-0 mt-1">${{ number_format($stats['total_revenue'], 2) }}</h3>
            <small class="text-muted mt-1">{{ $stats['bills_count'] }} Paid Invoices</small>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card shadow-sm border-0 border-start border-primary border-4 h-100 p-3">
            <span class="text-muted small fw-bold text-uppercase">Net Sales (Pre-Tax)</span>
            <h3 class="fw-bold text-primary mb-0 mt-1">${{ number_format($stats['net_sales'], 2) }}</h3>
            <small class="text-muted mt-1">Excluding Tax & Service</small>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card shadow-sm border-0 border-start border-info border-4 h-100 p-3">
            <span class="text-muted small fw-bold text-uppercase">Average Bill Value</span>
            <h3 class="fw-bold text-info mb-0 mt-1">${{ number_format($stats['avg_order_value'], 2) }}</h3>
            <small class="text-muted mt-1">Per Closed Order</small>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card shadow-sm border-0 border-start border-warning border-4 h-100 p-3">
            <span class="text-muted small fw-bold text-uppercase">Taxes & Service Fees</span>
            <h3 class="fw-bold text-dark mb-0 mt-1">${{ number_format($stats['tax_collected'] + $stats['service_fee'], 2) }}</h3>
            <small class="text-muted mt-1">VAT: ${{ number_format($stats['tax_collected'], 2) }} | Service: ${{ number_format($stats['service_fee'], 2) }}</small>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-graph-up text-primary me-2"></i>Daily Revenue Trend ($)</h6>
            </div>
            <div class="card-body">
                <div style="position: relative; height: 280px;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-pie-chart text-success me-2"></i>Payment Breakdown</h6>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                @if (count($paymentSplit) > 0)
                    <div style="position: relative; height: 250px; width: 100%;">
                        <canvas id="paymentChart"></canvas>
                    </div>
                @else
                    <p class="text-muted mb-0">No payments recorded in this period.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold"><i class="bi bi-trophy text-warning me-2"></i>Top 5 Best-Selling Items</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Item Name</th>
                    <th class="text-center">Total Quantity Sold</th>
                    <th class="text-end">Generated Revenue</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topItems as $item)
                    <tr>
                        <td class="fw-semibold text-dark">{{ $item['name'] }}</td>
                        <td class="text-center">
                            <span class="badge bg-secondary px-3 py-2 fs-6">{{ $item['total_quantity'] }}</span>
                        </td>
                        <td class="text-end fw-bold text-success">${{ number_format($item['total_revenue'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">No completed item sales recorded in this period.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: {
            labels: @json($revenueTrend['labels']),
            datasets: [{
                label: 'Gross Sales ($)',
                data: @json($revenueTrend['values']),
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.08)',
                borderWidth: 2,
                fill: true,
                tension: 0.35,
                pointRadius: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: value => '$' + value }
                }
            }
        }
    });

    const paymentCanvas = document.getElementById('paymentChart');
    if (paymentCanvas) {
        const paymentData = @json($paymentSplit);

        new Chart(paymentCanvas, {
            type: 'doughnut',
            data: {
                labels: Object.keys(paymentData).map(k => k.toUpperCase()),
                datasets: [{
                    data: Object.values(paymentData),
                    backgroundColor: ['#198754', '#0dcaf0', '#6c757d']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }
</script>
@endpush
