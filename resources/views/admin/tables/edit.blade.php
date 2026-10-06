@extends('layouts.app')

@section('title', 'Edit Table')
@section('page_title', 'Edit Table Details')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">Edit Table: {{ $table->table_number }}</h6>
                    <a href="{{ route('admin.tables.index') }}" class="btn btn-sm btn-outline-secondary">Back to List</a>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.tables.update', $table) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Table Number <span class="text-danger">*</span></label>
                                <input type="text" name="table_number" class="form-control"
                                    value="{{ old('table_number', $table->table_number) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Location / Area</label>
                                <input type="text" name="location" class="form-control"
                                    value="{{ old('location', $table->location) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Minimum Capacity <span class="text-danger">*</span></label>
                                <input type="number" name="min_capacity" class="form-control"
                                    value="{{ old('min_capacity', $table->min_capacity) }}" min="1" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Maximum Capacity <span class="text-danger">*</span></label>
                                <input type="number" name="max_capacity" class="form-control"
                                    value="{{ old('max_capacity', $table->max_capacity) }}" min="1" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label d-block">Table Type <span class="text-danger">*</span></label>
                                <div class="form-check form-check-inline mt-1">
                                    <input class="form-check-input" type="radio" name="type" id="typePublic"
                                        value="public" {{ old('type', $table->type) === 'public' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="typePublic">Public Seating</label>
                                </div>
                                <div class="form-check form-check-inline mt-1">
                                    <input class="form-check-input" type="radio" name="type" id="typePrivate"
                                        value="private" {{ old('type', $table->type) === 'private' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="typePrivate">Private / VIP</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    <option value="available"
                                        {{ old('status', $table->status) === 'available' ? 'selected' : '' }}>Available
                                    </option>
                                    <option value="occupied"
                                        {{ old('status', $table->status) === 'occupied' ? 'selected' : '' }}>Occupied
                                    </option>
                                    <option value="reserved"
                                        {{ old('status', $table->status) === 'reserved' ? 'selected' : '' }}>Reserved
                                    </option>
                                    <option value="maintenance"
                                        {{ old('status', $table->status) === 'maintenance' ? 'selected' : '' }}>Maintenance
                                    </option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $table->notes) }}</textarea>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary w-100">Update Table</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
