@extends('layouts.app')

@section('title', 'Billing & Payments')
@section('page_title', 'Billing & Cashier Terminal')

@section('content')
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-dark">
                <i class="bi bi-clock-history text-warning me-1"></i> Orders Ready for Billing
            </h6>
            <span class="badge bg-primary">{{ $pendingOrders->count() }} Orders</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Order #</th>
                        <th>Type / Table</th>
                        <th>Waiter</th>
                        <th>Items</th>
                        <th>Items Total</th>
                        <th>Order Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($pendingOrders->count() > 0)
                        @foreach ($pendingOrders as $order)
                            <tr>
                                <td class="fw-bold">{{ $order->order_number }}</td>
                                <td>
                                    @if ($order->order_type === 'dine_in')
                                        <span class="badge bg-primary">Table
                                            {{ $order->table->table_number ?? 'N/A' }}</span>
                                    @else
                                        <span class="badge bg-secondary">Takeaway</span>
                                    @endif
                                </td>
                                <td>{{ $order->user->name }}</td>
                                <td>{{ $order->items->count() }} items</td>
                                <td class="fw-bold text-dark">${{ number_format($order->total_amount, 2) }}</td>
                                <td>
                                    <span
                                        class="badge bg-info text-dark">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                                </td>
                                <td class="text-end">
                                    <form action="{{ route('billing.generate', $order) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="bi bi-receipt me-1"></i> Generate Bill
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No pending orders waiting for billing.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-receipt-cutoff me-1"></i> Invoices Log</h6>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('billing.index') }}" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- All Payment Statuses --</option>
                        <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                </div>
                @if (request('status'))
                    <div class="col-auto">
                        <a href="{{ route('billing.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
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
                        <th>Invoice #</th>
                        <th>Order #</th>
                        <th>Table</th>
                        <th>Cashier</th>
                        <th>Grand Total</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($bills->count() > 0)
                        @foreach ($bills as $bill)
                            <tr>
                                <td class="fw-bold">{{ $bill->invoice_number }}</td>
                                <td>{{ $bill->order->order_number }}</td>
                                <td>{{ $bill->table->table_number ?? 'Takeaway' }}</td>
                                <td>{{ $bill->cashier->name }}</td>
                                <td class="fw-bold text-success">${{ number_format($bill->total_amount, 2) }}</td>
                                <td>
                                    @if ($bill->status === 'paid')
                                        <span class="badge bg-success">Paid</span>
                                    @else
                                        <span class="badge bg-danger">Unpaid</span>
                                    @endif
                                </td>
                                <td class="small text-muted">{{ $bill->created_at->format('M d, h:i A') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('billing.show', $bill) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> View & Pay
                                    </a>
                                    @if ($bill->status === 'paid')
                                        <a href="{{ route('billing.receipt', $bill) }}"
                                            class="btn btn-sm btn-outline-dark ms-1">
                                            <i class="bi bi-printer"></i> Receipt
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No invoices recorded yet.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $bills->links('pagination::bootstrap-5') }}
    </div>
@endsection
