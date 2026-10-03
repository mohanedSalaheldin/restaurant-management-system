@extends('layouts.app')

@section('title', 'Menu Items')
@section('page_title', 'Menu Items')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 fw-bold">Menu Items</h5>
        <a href="{{ route('admin.menu-items.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Add New Item
        </a>
    </div>

    <!-- شريط الفلترة المتعدد والبحث -->
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.menu-items.index') }}" class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search by item name..."
                        value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="section_id" class="form-select">
                        <option value="">-- Section --</option>
                        @foreach ($sections as $sec)
                            <option value="{{ $sec->id }}" {{ request('section_id') == $sec->id ? 'selected' : '' }}>
                                {{ $sec->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="category_id" class="form-select">
                        <option value="">-- Category --</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="availability" class="form-select">
                        <option value="">-- Availability --</option>
                        <option value="available" {{ request('availability') == 'available' ? 'selected' : '' }}>Available
                        </option>
                        <option value="out_of_stock" {{ request('availability') == 'out_of_stock' ? 'selected' : '' }}>Out
                            of Stock</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-secondary w-50">Filter</button>
                    <a href="{{ route('admin.menu-items.index') }}" class="btn btn-outline-secondary w-50">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Hierarchy</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Tags</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>
                                <img src="{{ asset('storage/' . $item->image) }}" class="rounded" width="50"
                                    height="50" style="object-fit: cover;" alt="{{ $item->name }}">
                            </td>
                            <td>
                                <div class="fw-bold">{{ $item->name }}</div>
                                <div class="text-muted small">
                                    {{ $item->preparation_time ? $item->preparation_time . ' mins' : 'N/A' }}</div>
                            </td>
                            <td>
                                <div class="small fw-semibold">{{ $item->section->name }} &gt; {{ $item->category->name }}
                                </div>
                                <div class="text-muted small">{{ $item->subcategory->name }}</div>
                            </td>
                            <td class="fw-bold">${{ number_format($item->price, 2) }}</td>
                            <td>
                                @if ($item->availability === 'available')
                                    <span class="badge bg-success">Available</span>
                                @else
                                    <span class="badge bg-danger">Out of Stock</span>
                                @endif
                            </td>
                            <td>
                                @if ($item->special_tags)
                                    @foreach ($item->special_tags as $tag)
                                        <span class="badge bg-warning text-dark me-1">{{ $tag }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted small">None</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.menu-items.edit', $item) }}"
                                    class="btn btn-sm btn-outline-primary me-1">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <form action="{{ route('admin.menu-items.destroy', $item) }}" method="POST"
                                    class="d-inline-block"
                                    onsubmit="return confirm('Are you sure you want to delete this menu item?');">
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
                            <td colspan="7" class="text-center text-muted py-4">No menu items found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $items->links('pagination::bootstrap-5') }}
    </div>
@endsection
