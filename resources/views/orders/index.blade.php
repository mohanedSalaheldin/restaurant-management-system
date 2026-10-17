@extends('layouts.app')

@section('title', 'Orders')
@section('page_title', 'Orders Management')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 fw-bold">All Orders</h5>
        <a href="{{ route('orders.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Create Order
        </a>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('orders.index') }}" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">-- All Statuses --</option>
                        @foreach (['draft', 'sent_to_kitchen', 'in_preparation', 'ready', 'served', 'completed', 'cancelled'] as $st)
                            <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $st)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="order_type" class="form-select" onchange="this.form.submit()">
                        <option value="">-- All Types --</option>
                        <option value="dine_in" {{ request('order_type') === 'dine_in' ? 'selected' : '' }}>Dine-in</option>
                        <option value="takeaway" {{ request('order_type') === 'takeaway' ? 'selected' : '' }}>Takeaway
                        </option>
                    </select>
                </div>
                @if (request('status') || request('order_type'))
                    <div class="col-auto">
                        <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Order #</th>
                        <th>Type / Table</th>
                        <th>Waiter</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Time</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $ord)
                        <tr>
                            <td class="fw-bold">{{ $ord->order_number }}</td>
                            <td>
                                @if ($ord->order_type === 'dine_in')
                                    <span class="badge bg-primary">Table {{ $ord->table->table_number ?? 'N/A' }}</span>
                                @else
                                    <span class="badge bg-secondary">Takeaway</span>
                                @endif
                            </td>
                            <td>{{ $ord->user->name }}</td>
                            <td>{{ $ord->items->count() }} items</td>
                            <td class="fw-bold text-success">${{ number_format($ord->total_amount, 2) }}</td>
                            <td>
                                @php
                                    $badgeClass = match ($ord->status) {
                                        'draft' => 'bg-secondary',
                                        'sent_to_kitchen' => 'bg-info text-dark',
                                        'in_preparation' => 'bg-warning text-dark',
                                        'ready' => 'bg-success',
                                        'served' => 'bg-primary',
                                        'completed' => 'bg-dark',
                                        'cancelled' => 'bg-danger',
                                        default => 'bg-light',
                                    };
                                @endphp
                                <span
                                    class="badge {{ $badgeClass }}">{{ ucfirst(str_replace('_', ' ', $ord->status)) }}</span>
                            </td>
                            <td class="small text-muted">{{ $ord->created_at->format('h:i A') }}</td>
                            <td class="text-end">
                                <a href="{{ route('orders.show', $ord) }}" class="btn btn-sm btn-outline-primary">
                                    View Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No orders found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $orders->links('pagination::bootstrap-5') }}
    </div>
@endsection
