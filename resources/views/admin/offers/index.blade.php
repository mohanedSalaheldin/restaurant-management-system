@extends('layouts.app')

@section('title', 'Offers Management')
@section('page_title', 'Offers & Discounts')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 fw-bold">Active & Scheduled Offers</h5>
        <a href="{{ route('admin.offers.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Add New Offer
        </a>
    </div>

    <!-- Filters Form -->
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('admin.offers.index') }}" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">-- All Statuses --</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                @if (request('status'))
                    <div class="col-auto">
                        <a href="{{ route('admin.offers.index') }}" class="btn btn-outline-secondary">Clear Filter</a>
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
                        <th>Offer Name</th>
                        <th>Discount</th>
                        <th>Validity Period</th>
                        <th>Items</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($offers as $offer)
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $offer->name }}</div>
                                <div class="text-muted small">{{ Str::limit($offer->description, 40) ?? 'No description' }}
                                </div>
                            </td>
                            <td>
                                @if ($offer->discount_type === 'percentage')
                                    <span class="badge bg-danger">{{ (int) $offer->discount_value }}% OFF</span>
                                @else
                                    <span class="badge bg-success">${{ number_format($offer->discount_value, 2) }}
                                        OFF</span>
                                @endif
                            </td>
                            <td>
                                <div class="small fw-semibold">{{ $offer->start_date->format('M d, Y') }} -
                                    {{ $offer->end_date->format('M d, Y') }}</div>
                                @if ($offer->start_time && $offer->end_time)
                                    <div class="text-muted small"><i
                                            class="bi bi-clock me-1"></i>{{ \Carbon\Carbon::parse($offer->start_time)->format('h:i A') }}
                                        - {{ \Carbon\Carbon::parse($offer->end_time)->format('h:i A') }}</div>
                                @endif
                                @if (!empty($offer->applicable_days))
                                    <div class="text-primary small fw-semibold">
                                        {{ implode(', ', $offer->applicable_days) }}</div>
                                @else
                                    <div class="text-muted small">All Days</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info text-dark">{{ $offer->items_count }} Items</span>
                            </td>
                            <td>
                                @if ($offer->status)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.offers.edit', $offer) }}"
                                    class="btn btn-sm btn-outline-primary me-1">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <form action="{{ route('admin.offers.destroy', $offer) }}" method="POST"
                                    class="d-inline-block" onsubmit="return confirm('Delete this offer?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No offers created yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $offers->links('pagination::bootstrap-5') }}
    </div>
@endsection
