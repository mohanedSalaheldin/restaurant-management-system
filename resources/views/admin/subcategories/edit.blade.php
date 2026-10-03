@extends('layouts.app')

@section('title', 'Edit Subcategory')
@section('page_title', 'Edit Subcategory')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">Edit: {{ $subcategory->name }}</h6>
                    <a href="{{ route('admin.subcategories.index') }}" class="btn btn-sm btn-outline-secondary">Back to
                        List</a>
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

                    <form action="{{ route('admin.subcategories.update', $subcategory) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" required>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ old('category_id', $subcategory->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }} ({{ $category->section->name }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Subcategory Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control"
                                value="{{ old('name', $subcategory->name) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description', $subcategory->description) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Display Order</label>
                            <input type="number" name="display_order" class="form-control"
                                value="{{ old('display_order', $subcategory->display_order) }}" min="0">
                        </div>

                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" name="status" id="statusSwitch" value="1"
                                {{ old('status', $subcategory->status) ? 'checked' : '' }}>
                            <label class="form-check-label" for="statusSwitch">Active Status</label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Update Subcategory</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
