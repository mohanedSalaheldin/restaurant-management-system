@extends('layouts.app')

@section('title', 'Tables Management')
@section('page_title', 'Tables Management')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 fw-bold">Restaurant Tables</h5>
        <a href="{{ route('admin.tables.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Add New Table
        </a>
    </div>

    <!-- Filters Form -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('admin.tables.index') }}" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <select name="type" class="form-select" onchange="this.form.submit()">
                        <option value="">-- All Types --</option>
                        <option value="public" {{ request('type') === 'public' ? 'selected' : '' }}>Public Seating</option>
                        <option value="private" {{ request('type') === 'private' ? 'selected' : '' }}>Private / VIP</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">-- All Statuses --</option>
                        <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Available
                        </option>
                        <option value="occupied" {{ request('status') === 'occupied' ? 'selected' : '' }}>Occupied</option>
                        <option value="reserved" {{ request('status') === 'reserved' ? 'selected' : '' }}>Reserved</option>
                        <option value="maintenance" {{ request('status') === 'maintenance' ? 'selected' : '' }}>Maintenance
                        </option>
                    </select>
                </div>
                @if (request('type') || request('status'))
                    <div class="col-auto">
                        <a href="{{ route('admin.tables.index') }}" class="btn btn-outline-secondary">Clear Filters</a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Tables Grid -->
    <div class="row g-3">
        @if ($tables->count() > 0)
            @foreach ($tables as $table)
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark">{{ $table->table_number }}</h5>
                                    <span
                                        class="badge {{ $table->type === 'private' ? 'bg-dark' : 'bg-secondary' }} small">
                                        {{ ucfirst($table->type) }}
                                    </span>
                                </div>
                                <div>
                                    @if ($table->status === 'available')
                                        <span class="badge bg-success">Available</span>
                                    @elseif($table->status === 'occupied')
                                        <span class="badge bg-danger">Occupied</span>
                                    @elseif($table->status === 'reserved')
                                        <span class="badge bg-warning text-dark">Reserved</span>
                                    @else
                                        <span class="badge bg-secondary">Maintenance</span>
                                    @endif
                                </div>
                            </div>

                            <div class="text-muted small mb-3">
                                <div><i class="bi bi-people me-1"></i> Capacity: <strong>{{ $table->min_capacity }} -
                                        {{ $table->max_capacity }} Seats</strong></div>
                                <div><i class="bi bi-geo-alt me-1"></i> Area:
                                    <strong>{{ $table->location ?? 'General Area' }}</strong></div>
                                @if ($table->notes)
                                    <div class="text-truncate mt-1"><i class="bi bi-info-circle me-1"></i>
                                        {{ $table->notes }}</div>
                                @endif
                            </div>

                            <div class="mt-auto d-flex justify-content-between align-items-center border-top pt-2">
                                <button type="button" class="btn btn-sm btn-outline-dark" data-bs-toggle="modal"
                                    data-bs-target="#qrModal-{{ $table->id }}">
                                    <i class="bi bi-qr-code me-1"></i> View QR
                                </button>

                                <div>
                                    <a href="{{ route('admin.tables.edit', $table) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.tables.destroy', $table) }}" method="POST"
                                        class="d-inline-block" onsubmit="return confirm('Delete this table?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- QR Code Modal -->
                <div class="modal fade" id="qrModal-{{ $table->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content text-center">
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title fw-bold">QR Code: {{ $table->table_number }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-4" id="printable-area-{{ $table->id }}">
                                <div class="p-3 border rounded bg-white d-inline-block shadow-sm mb-3">
                                    {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(220)->generate($table->qr_url) !!}
                                </div>
                                <h6 class="fw-bold mb-1">Scan for Menu</h6>
                                <p class="text-muted small mb-0">
                                    {{ $table->location ? $table->location . ' - ' : '' }}{{ ucfirst($table->type) }}
                                    Table</p>
                            </div>
                            <div class="modal-footer border-0 pt-0 d-flex justify-content-between">
                                <form action="{{ route('admin.tables.regenerate.qr', $table) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-warning"
                                        onclick="return confirm('Regenerating will invalidate the previous printed QR. Continue?');">
                                        <i class="bi bi-arrow-clockwise"></i> Regenerate
                                    </button>
                                </form>

                                <button type="button" class="btn btn-sm btn-primary"
                                    onclick="printQr('printable-area-{{ $table->id }}')">
                                    <i class="bi bi-printer me-1"></i> Print Label
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="col-12">
                <div class="card shadow-sm border-0 text-center py-5 text-muted">
                    No tables registered yet. Start by adding one.
                </div>
            </div>
        @endif
    </div>

    <div class="mt-4">
        {{ $tables->links('pagination::bootstrap-5') }}
    </div>
@endsection

@push('scripts')
    <script>
        function printQr(elementId) {
            const printContent = document.getElementById(elementId).innerHTML;
            const originalContent = document.body.innerHTML;
            document.body.innerHTML = `<div style="text-align:center; padding-top: 50px;">${printContent}</div>`;
            window.print();
            document.body.innerHTML = originalContent;
            window.location.reload();
        }
    </script>
@endpush
