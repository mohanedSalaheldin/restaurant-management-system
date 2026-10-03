@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('page_title', 'Admin Overview')

@section('content')
<!-- بطاقات الإحصائيات المطلوبة في المشروع -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-sm-6">
        <div class="card shadow-sm border-0 border-start border-success border-4">
            <div class="card-body">
                <div class="text-muted small fw-semibold">Today's Revenue</div>
                <div class="fs-4 fw-bold text-success">$0.00</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card shadow-sm border-0 border-start border-primary border-4">
            <div class="card-body">
                <div class="text-muted small fw-semibold">Total Orders</div>
                <div class="fs-4 fw-bold text-primary">0</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card shadow-sm border-0 border-start border-danger border-4">
            <div class="card-body">
                <div class="text-muted small fw-semibold">Occupied Tables</div>
                <div class="fs-4 fw-bold text-danger">0 / 0</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card shadow-sm border-0 border-start border-warning border-4">
            <div class="card-body">
                <div class="text-muted small fw-semibold">Pending Orders</div>
                <div class="fs-4 fw-bold text-warning">0</div>
            </div>
        </div>
    </div>
</div>

<!-- جدول أحدث 10 طلبات والروابط السريعة -->
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">Recent Orders (Last 10)</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Order #</th>
                                <th>Table</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No recent orders found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">Quick Actions</h6>
            </div>
            <div class="card-body d-flex flex-column gap-2">
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary text-start">
                    <i class="bi bi-people me-2"></i>Manage Users
                </a>
                <a href="#" class="btn btn-outline-secondary text-start">
                    <i class="bi bi-journal-text me-2"></i>Manage Menu
                </a>
                <a href="#" class="btn btn-outline-secondary text-start">
                    <i class="bi bi-grid-3x3-gap me-2"></i>Manage Tables
                </a>
                <a href="#" class="btn btn-outline-secondary text-start">
                    <i class="bi bi-bar-chart me-2"></i>View Reports
                </a>
            </div>
        </div>
    </div>
</div>
@endsection