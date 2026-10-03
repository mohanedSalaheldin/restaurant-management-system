@extends('layouts.app')

@section('title', 'Add Menu Item')
@section('page_title', 'Add Menu Item')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">Menu Item Details</h6>
                    <a href="{{ route('admin.menu-items.index') }}" class="btn btn-sm btn-outline-secondary">Back to List</a>
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

                    <form action="{{ route('admin.menu-items.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Item Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ old('name') }}"
                                    placeholder="e.g. Espresso Romano" maxlength="200" required>
                            </div>

                            <!-- Cascading Dropdowns -->
                            <div class="col-md-4">
                                <label class="form-label">Section <span class="text-danger">*</span></label>
                                <select id="section_select" name="section_id" class="form-select" required>
                                    <option value="">-- Choose Section --</option>
                                    @foreach ($sections as $sec)
                                        <option value="{{ $sec->id }}"
                                            {{ old('section_id') == $sec->id ? 'selected' : '' }}>{{ $sec->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                <select id="category_select" name="category_id" class="form-select" required>
                                    <option value="">-- Choose Category --</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Subcategory <span class="text-danger">*</span></label>
                                <select id="subcategory_select" name="subcategory_id" class="form-select" required>
                                    <option value="">-- Choose Subcategory --</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Price ($) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" name="price" class="form-control"
                                    value="{{ old('price') }}" placeholder="0.00" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Preparation Time (minutes)</label>
                                <input type="number" name="preparation_time" class="form-control"
                                    value="{{ old('preparation_time') }}" placeholder="e.g. 15">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Availability <span class="text-danger">*</span></label>
                                <select name="availability" class="form-select" required>
                                    <option value="available" {{ old('availability') === 'available' ? 'selected' : '' }}>
                                        Available</option>
                                    <option value="out_of_stock"
                                        {{ old('availability') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Item Image <span class="text-danger">*</span> (Max: 2MB)</label>
                                <input type="file" name="image" class="form-control" accept="image/png, image/jpeg"
                                    required>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Description (Max 500 chars)</label>
                                <textarea name="description" class="form-control" rows="3" maxlength="500">{{ old('description') }}</textarea>
                            </div>

                            <!-- Special Tags Checkboxes -->
                            <div class="col-md-12">
                                <label class="form-label d-block fw-semibold">Special Tags</label>
                                @php
                                    $availableTags = ['Vegetarian', 'Vegan', 'Spicy', "Chef's Special", 'Popular'];
                                @endphp
                                <div class="d-flex flex-wrap gap-3">
                                    @foreach ($availableTags as $tag)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="special_tags[]"
                                                value="{{ $tag }}" id="tag_{{ $loop->index }}"
                                                {{ is_array(old('special_tags')) && in_array($tag, old('special_tags')) ? 'checked' : '' }}>
                                            <label class="form-check-label"
                                                for="tag_{{ $loop->index }}">{{ $tag }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-success w-100">Save Menu Item</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const sectionSelect = document.getElementById('section_select');
        const categorySelect = document.getElementById('category_select');
        const subcategorySelect = document.getElementById('subcategory_select');

        sectionSelect.addEventListener('change', function() {
            const sectionId = this.value;
            categorySelect.innerHTML = '<option value="">Loading...</option>';
            subcategorySelect.innerHTML = '<option value="">-- Choose Subcategory --</option>';

            if (sectionId) {
                fetch(`/admin/sections/${sectionId}/categories-data`)
                    .then(res => res.json())
                    .then(data => {
                        categorySelect.innerHTML = '<option value="">-- Choose Category --</option>';
                        data.forEach(cat => {
                            categorySelect.innerHTML +=
                            `<option value="${cat.id}">${cat.name}</option>`;
                        });
                    });
            } else {
                categorySelect.innerHTML = '<option value="">-- Choose Category --</option>';
            }
        });

        categorySelect.addEventListener('change', function() {
            const categoryId = this.value;
            subcategorySelect.innerHTML = '<option value="">Loading...</option>';

            if (categoryId) {
                fetch(`/admin/categories/${categoryId}/subcategories-data`)
                    .then(res => res.json())
                    .then(data => {
                        subcategorySelect.innerHTML = '<option value="">-- Choose Subcategory --</option>';
                        data.forEach(sub => {
                            subcategorySelect.innerHTML +=
                                `<option value="${sub.id}">${sub.name}</option>`;
                        });
                    });
            } else {
                subcategorySelect.innerHTML = '<option value="">-- Choose Subcategory --</option>';
            }
        });
    </script>
@endpush
