@extends('layouts.app')

@section('page_title', 'Waiter Dashboard')

@section('content')
    <div class="row g-3">
        <div class="col-md-6">
            <a href="{{ route('orders.create') }}" class="card shadow-sm border-0 p-4 text-decoration-none text-dark h-100">
                <h5 class="fw-bold mb-1"><i class="bi bi-plus-circle text-success me-2"></i>Create Order</h5>
                <span class="text-muted small">Take a dine-in or takeaway order and send it to the kitchen.</span>
            </a>
        </div>
        <div class="col-md-6">
            <a href="{{ route('orders.index') }}" class="card shadow-sm border-0 p-4 text-decoration-none text-dark h-100">
                <h5 class="fw-bold mb-1"><i class="bi bi-receipt text-primary me-2"></i>View Orders</h5>
                <span class="text-muted small">Follow order status and mark ready orders as served.</span>
            </a>
        </div>
    </div>
@endsection
