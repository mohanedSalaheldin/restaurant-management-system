@extends('layouts.app')

@section('page_title', 'Cashier Dashboard')

@section('content')
    <div class="row g-3">
        <div class="col-md-6">
            <a href="{{ route('billing.index') }}" class="card shadow-sm border-0 p-4 text-decoration-none text-dark h-100">
                <h5 class="fw-bold mb-1"><i class="bi bi-receipt text-success me-2"></i>Billing Terminal</h5>
                <span class="text-muted small">Generate invoices for ready orders and record payments.</span>
            </a>
        </div>
    </div>
@endsection
