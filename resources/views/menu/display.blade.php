@extends('layouts.app')

@section('title', 'Browse Menu')
@section('page_title', 'Restaurant Menu')

@section('content')
    <div class="container-fluid p-0">
        @if ($sections->isEmpty())
            <div class="alert alert-warning text-center">
                No active menu sections found. Please add sections, categories, and items first.
            </div>
        @else
            <!-- 1. Sections Pills (Click Section -> Show Categories) -->
            <ul class="nav nav-pills nav-fill bg-white p-2 rounded shadow-sm mb-4" id="sectionTabs" role="tablist">
                @foreach ($sections as $secIndex => $section)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold {{ $secIndex === 0 ? 'active' : '' }}"
                            id="section-tab-{{ $section->id }}" data-bs-toggle="pill"
                            data-bs-target="#section-pane-{{ $section->id }}" type="button" role="tab">
                            <i class="bi bi-folder2-open me-1"></i> {{ $section->name }}
                        </button>
                    </li>
                @endforeach
            </ul>

            <!-- Sections Content -->
            <div class="tab-content" id="sectionTabsContent">
                @foreach ($sections as $secIndex => $section)
                    <div class="tab-pane fade {{ $secIndex === 0 ? 'show active' : '' }}"
                        id="section-pane-{{ $section->id }}" role="tabpanel">

                        @if ($section->categories->isEmpty())
                            <div class="card shadow-sm border-0 text-center py-5 text-muted">
                                No categories available under {{ $section->name }}.
                            </div>
                        @else
                            <!-- 2. Categories Navigation -->
                            <div class="accordion mb-4" id="accordionCategory-{{ $section->id }}">
                                @foreach ($section->categories as $catIndex => $category)
                                    <div class="accordion-item shadow-sm border-0 mb-3 rounded overflow-hidden">
                                        <h2 class="accordion-header" id="heading-cat-{{ $category->id }}">
                                            <button
                                                class="accordion-button {{ $catIndex !== 0 ? 'collapsed' : '' }} fw-semibold fs-5 bg-light"
                                                type="button" data-bs-toggle="collapse"
                                                data-bs-target="#collapse-cat-{{ $category->id }}"
                                                aria-expanded="{{ $catIndex === 0 ? 'true' : 'false' }}">
                                                <i class="bi bi-tag me-2 text-primary"></i> {{ $category->name }}
                                                <span
                                                    class="badge bg-secondary ms-2 fs-6">{{ $category->subcategories->count() }}
                                                    Subcategories</span>
                                            </button>
                                        </h2>

                                        <div id="collapse-cat-{{ $category->id }}"
                                            class="accordion-collapse collapse {{ $catIndex === 0 ? 'show' : '' }}"
                                            data-bs-parent="#accordionCategory-{{ $section->id }}">
                                            <div class="accordion-body bg-white p-4">

                                                @if ($category->subcategories->isEmpty())
                                                    <div class="text-muted text-center py-3">No subcategories in this
                                                        category.</div>
                                                @else
                                                    <!-- 3. Subcategories Tabs / Buttons -->
                                                    <div class="d-flex flex-wrap gap-2 mb-4 border-bottom pb-3">
                                                        @foreach ($category->subcategories as $subIndex => $subcategory)
                                                            <button
                                                                class="btn btn-outline-primary btn-sm subcat-filter-btn {{ $subIndex === 0 ? 'active' : '' }}"
                                                                data-target="subcat-container-{{ $subcategory->id }}">
                                                                {{ $subcategory->name }}
                                                                ({{ $subcategory->items->count() }})
                                                            </button>
                                                        @endforeach
                                                    </div>

                                                    <!-- 4. Items Cards Display -->
                                                    @foreach ($category->subcategories as $subIndex => $subcategory)
                                                        <div class="subcategory-items-block {{ $subIndex !== 0 ? 'd-none' : '' }}"
                                                            id="subcat-container-{{ $subcategory->id }}">

                                                            <h6 class="fw-bold text-secondary mb-3">
                                                                <i class="bi bi-arrow-right-short"></i>
                                                                {{ $subcategory->name }}
                                                            </h6>

                                                            @if ($subcategory->items->isEmpty())
                                                                <div class="text-muted py-3">No items available under this
                                                                    subcategory.</div>
                                                            @else
                                                                <div class="row g-3">
                                                                    @foreach ($subcategory->items as $item)
                                                                        <div class="col-xl-3 col-lg-4 col-md-6">
                                                                            <div
                                                                                class="card h-100 shadow-sm border-0 rounded-3">
                                                                                <img src="{{ asset('storage/' . $item->image) }}"
                                                                                    class="card-img-top"
                                                                                    style="height: 180px; object-fit: cover;"
                                                                                    alt="{{ $item->name }}">

                                                                                <div class="card-body d-flex flex-column">
                                                                                    <div
                                                                                        class="d-flex justify-content-between align-items-start mb-2">
                                                                                        <h6
                                                                                            class="card-title fw-bold mb-0 text-dark">
                                                                                            {{ $item->name }}</h6>
                                                                                        <span
                                                                                            class="fs-6 fw-bold text-success">${{ number_format($item->price, 2) }}</span>
                                                                                    </div>

                                                                                    <p
                                                                                        class="card-text text-muted small flex-grow-1">
                                                                                        {{ $item->description ? Str::limit($item->description, 80) : 'Freshly prepared upon your request.' }}
                                                                                    </p>

                                                                                    <!-- Special Tags -->
                                                                                    <div class="mb-3">
                                                                                        @if ($item->special_tags)
                                                                                            @foreach ($item->special_tags as $tag)
                                                                                                <span
                                                                                                    class="badge bg-warning text-dark small">{{ $tag }}</span>
                                                                                            @endforeach
                                                                                        @endif
                                                                                    </div>

                                                                                    <!-- Add to Order Button -->
                                                                                    <button
                                                                                        class="btn btn-outline-primary btn-sm w-100"
                                                                                        onclick="alert('Item ({{ $item->name }}) ready to add! Full ordering flow will be unlocked in Week 3.')">
                                                                                        <i class="bi bi-cart-plus me-1"></i>
                                                                                        Add to Order
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                @endif

                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        // تفعيل التبديل بين التصنيفات الفرعية وعرض أصنافها بسلاسة
        document.addEventListener('DOMContentLoaded', function() {
            const filterButtons = document.querySelectorAll('.subcat-filter-btn');

            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const parentAccordionBody = this.closest('.accordion-body');

                    // إلغاء تفعيل كل الأزرار داخل هذا التصنيف
                    parentAccordionBody.querySelectorAll('.subcat-filter-btn').forEach(btn => btn
                        .classList.remove('active'));
                    this.classList.add('active');

                    // إخفاء كل مجموعات الأصناف داخل هذا التصنيف وإظهار المجموعة المطلوبة
                    const targetId = this.getAttribute('data-target');
                    parentAccordionBody.querySelectorAll('.subcategory-items-block').forEach(
                        block => block.classList.add('d-none'));

                    const targetBlock = document.getElementById(targetId);
                    if (targetBlock) {
                        targetBlock.classList.remove('d-none');
                    }
                });
            });
        });
    </script>
@endpush
