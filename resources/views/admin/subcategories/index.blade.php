@extends('layouts.app')

@section('title', 'Subcategories')
@section('page_title', 'Menu Subcategories')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 fw-bold">Menu Subcategories</h5>
        <a href="{{ route('admin.subcategories.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Add Subcategory
        </a>
    </div>

    <!-- فلترة بحسب القسم والتصنيف -->
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('admin.subcategories.index') }}" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <select name="section_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- All Sections --</option>
                        @foreach ($sections as $sec)
                            <option value="{{ $sec->id }}" {{ request('section_id') == $sec->id ? 'selected' : '' }}>
                                {{ $sec->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <select name="category_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- All Categories --</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @if (request('section_id') || request('category_id'))
                    <div class="col-auto">
                        <a href="{{ route('admin.subcategories.index') }}" class="btn btn-outline-secondary">Reset</a>
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
                        <th>Order</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Section</th>
                        <th>Items</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subcategories as $sub)
                        <tr>
                            <td><span class="badge bg-secondary">{{ $sub->display_order }}</span></td>
                            <td class="fw-semibold">{{ $sub->name }}</td>
                            <td><span class="badge bg-info text-dark">{{ $sub->category->name }}</span></td>
                            <td><span class="badge bg-light text-dark border">{{ $sub->category->section->name }}</span>
                            </td>
                            <td>{{ $sub->items_count }}</td>
                            <td>
                                @if ($sub->status)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.subcategories.edit', $sub) }}"
                                    class="btn btn-sm btn-outline-primary me-1">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <form action="{{ route('admin.subcategories.destroy', $sub) }}" method="POST"
                                    class="d-inline-block"
                                    onsubmit="return confirm('Are you sure you want to delete this subcategory?');">
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
                            <td colspan="7" class="text-center text-muted py-4">No subcategories found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $subcategories->links('pagination::bootstrap-5') }}
    </div>
@endsection
