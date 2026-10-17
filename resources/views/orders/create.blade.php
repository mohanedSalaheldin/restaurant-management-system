@extends('layouts.app')

@section('title', 'New Order')
@section('page_title', 'Create New Order')

@section('content')
    <form action="{{ route('orders.store') }}" method="POST" id="orderForm">
        @csrf
        <div class="row g-3">
            <!-- القسم الأيسر: تصفح واختيار الأصناف -->
            <div class="col-lg-7">
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body">
                        <div class="row g-2 align-items-center mb-3">
                            <div class="col-md-6">
                                <input type="text" id="itemSearch" class="form-control" placeholder="Search menu item...">
                            </div>
                            <div class="col-md-6">
                                <select id="categoryFilter" class="form-select">
                                    <option value="">-- All Categories --</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- شبكة الأصناف المتاحة -->
                        <div class="row g-2 overflow-auto" style="max-height: 550px;" id="menuItemsGrid">
                            @foreach ($menuItems as $item)
                                <div class="col-md-4 col-sm-6 menu-item-card" data-name="{{ strtolower($item->name) }}"
                                    data-category="{{ $item->category_id }}">
                                    <div class="card h-100 border p-2 text-center shadow-none position-relative"
                                        style="cursor: pointer;"
                                        onclick="addToCart({{ $item->id }}, '{{ addslashes($item->name) }}', {{ $item->final_price }}, {{ $item->price }})">

                                        <img src="{{ asset('storage/' . $item->image) }}" class="rounded mb-2 w-100"
                                            style="height: 90px; object-fit: cover;" alt="{{ $item->name }}">

                                        <h6 class="fw-bold mb-1 fs-6 text-truncate" title="{{ $item->name }}">
                                            {{ $item->name }}</h6>

                                        <div>
                                            @if ($item->active_offer)
                                                <span
                                                    class="text-decoration-line-through text-muted small me-1">${{ number_format($item->price, 2) }}</span>
                                                <span
                                                    class="text-danger fw-bold">${{ number_format($item->final_price, 2) }}</span>
                                            @else
                                                <span
                                                    class="text-success fw-bold">${{ number_format($item->price, 2) }}</span>
                                            @endif
                                        </div>

                                        @if ($item->active_offer)
                                            <span class="position-absolute top-0 end-0 badge bg-danger m-1"
                                                style="font-size: 0.65rem;">OFFER</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- القسم الأيمن: تفاصيل الطلب والسلة -->
            <div class="col-lg-5">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-cart3 me-1"></i> Order Summary & Table</h6>
                    </div>
                    <div class="card-body">
                        <!-- تفاصيل نوع الطلب والطاولة -->
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Order Type</label>
                                <select name="order_type" id="orderType" class="form-select form-select-sm" required
                                    onchange="toggleTableSelection()">
                                    <option value="dine_in">Dine-in</option>
                                    <option value="takeaway">Takeaway</option>
                                </select>
                            </div>
                            <div class="col-6" id="tableGroup">
                                <label class="form-label small fw-semibold">Table</label>
                                <select name="table_id" id="tableSelect" class="form-select form-select-sm" required>
                                    <option value="">-- Choose Table --</option>
                                    @foreach ($tables as $tbl)
                                        <option value="{{ $tbl->id }}">{{ $tbl->table_number }}
                                            ({{ $tbl->max_capacity }} seats)</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6" id="guestGroup">
                                <label class="form-label small fw-semibold">Guests Count</label>
                                <input type="number" name="guest_count" class="form-control form-select-sm" value="1"
                                    min="1">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Notes</label>
                                <input type="text" name="notes" class="form-control form-select-sm"
                                    placeholder="General order notes">
                            </div>
                        </div>

                        <!-- سلة الأصناف المضافة -->
                        <h6 class="fw-bold border-bottom pb-2 small text-secondary">Ordered Items</h6>
                        <div id="cartItemsContainer" class="overflow-auto mb-3" style="max-height: 280px;">
                            <p class="text-muted small text-center py-4" id="emptyCartText">No items added yet. Click on
                                menu items to add.</p>
                        </div>

                        <!-- ملخص الحساب -->
                        <div class="border-top pt-2 mb-3">
                            <div class="d-flex justify-content-between text-muted small mb-1">
                                <span>Subtotal:</span>
                                <span id="cartSubtotal">$0.00</span>
                            </div>
                            <div class="d-flex justify-content-between text-danger small mb-1">
                                <span>Total Discount:</span>
                                <span id="cartDiscount">-$0.00</span>
                            </div>
                            <div class="d-flex justify-content-between fw-bold fs-5 text-dark border-top pt-1">
                                <span>Net Total:</span>
                                <span id="cartTotal" class="text-success">$0.00</span>
                            </div>
                        </div>

                        <!-- أزرار الإجراءات -->
                        <div class="d-flex gap-2">
                            <button type="submit" name="status" value="draft" class="btn btn-outline-secondary w-50">
                                <i class="bi bi-save me-1"></i> Save Draft
                            </button>
                            <button type="submit" name="status" value="sent_to_kitchen" class="btn btn-success w-50">
                                <i class="bi bi-send-fill me-1"></i> Send to Kitchen
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        let cart = {};

        function toggleTableSelection() {
            const type = document.getElementById('orderType').value;
            const tableGroup = document.getElementById('tableGroup');
            const guestGroup = document.getElementById('guestGroup');
            const tableSelect = document.getElementById('tableSelect');

            if (type === 'takeaway') {
                tableGroup.classList.add('d-none');
                guestGroup.classList.add('d-none');
                tableSelect.removeAttribute('required');
                tableSelect.value = '';
            } else {
                tableGroup.classList.remove('d-none');
                guestGroup.classList.remove('d-none');
                tableSelect.setAttribute('required', 'required');
            }
        }

        function addToCart(id, name, finalPrice, originalPrice) {
            if (cart[id]) {
                cart[id].quantity += 1;
            } else {
                cart[id] = {
                    id: id,
                    name: name,
                    finalPrice: finalPrice,
                    originalPrice: originalPrice,
                    quantity: 1,
                    instructions: ''
                };
            }
            renderCart();
        }

        function updateQty(id, delta) {
            if (cart[id]) {
                cart[id].quantity += delta;
                if (cart[id].quantity <= 0) {
                    delete cart[id];
                }
                renderCart();
            }
        }

        function updateInstructions(id, text) {
            if (cart[id]) {
                cart[id].instructions = text;
            }
        }

        function renderCart() {
            const container = document.getElementById('cartItemsContainer');
            const emptyText = document.getElementById('emptyCartText');
            const keys = Object.keys(cart);

            if (keys.length === 0) {
                container.innerHTML =
                    '<p class="text-muted small text-center py-4" id="emptyCartText">No items added yet. Click on menu items to add.</p>';
                document.getElementById('cartSubtotal').innerText = '$0.00';
                document.getElementById('cartDiscount').innerText = '-$0.00';
                document.getElementById('cartTotal').innerText = '$0.00';
                return;
            }

            let html = '';
            let subtotal = 0;
            let totalDiscount = 0;

            keys.forEach((key, index) => {
                const item = cart[key];
                const lineSubtotal = item.finalPrice * item.quantity;
                const lineDiscount = (item.originalPrice - item.finalPrice) * item.quantity;

                subtotal += (item.originalPrice * item.quantity);
                totalDiscount += lineDiscount;

                html += `
                <div class="card p-2 mb-2 bg-light border-0">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold small">${item.name}</span>
                        <span class="fw-bold text-dark small">$${lineSubtotal.toFixed(2)}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="input-group input-group-sm" style="width: 100px;">
                            <button type="button" class="btn btn-outline-secondary" onclick="updateQty(${item.id}, -1)">-</button>
                            <input type="text" class="form-control text-center" value="${item.quantity}" readonly>
                            <button type="button" class="btn btn-outline-secondary" onclick="updateQty(${item.id}, 1)">+</button>
                        </div>
                        <input type="text" class="form-control form-control-sm ms-2" placeholder="Instructions (e.g. no salt)" 
                               value="${item.instructions}" onchange="updateInstructions(${item.id}, this.value)">
                    </div>
                    <!-- Hidden inputs for Form Submission -->
                    <input type="hidden" name="items[${index}][menu_item_id]" value="${item.id}">
                    <input type="hidden" name="items[${index}][quantity]" value="${item.quantity}">
                    <input type="hidden" name="items[${index}][special_instructions]" value="${item.instructions}">
                </div>
            `;
            });

            container.innerHTML = html;
            document.getElementById('cartSubtotal').innerText = `$${subtotal.toFixed(2)}`;
            document.getElementById('cartDiscount').innerText = `-$${totalDiscount.toFixed(2)}`;
            document.getElementById('cartTotal').innerText = `$${(subtotal - totalDiscount).toFixed(2)}`;
        }

        // فلترة الأصناف في الواجهة
        document.getElementById('itemSearch').addEventListener('input', filterMenu);
        document.getElementById('categoryFilter').addEventListener('change', filterMenu);

        function filterMenu() {
            const query = document.getElementById('itemSearch').value.toLowerCase();
            const cat = document.getElementById('categoryFilter').value;
            const cards = document.querySelectorAll('.menu-item-card');

            cards.forEach(card => {
                const name = card.getAttribute('data-name');
                const category = card.getAttribute('data-category');
                const matchName = name.includes(query);
                const matchCat = cat === '' || category === cat;

                if (matchName && matchCat) {
                    card.classList.remove('d-none');
                } else {
                    card.classList.add('d-none');
                }
            });
        }
    </script>
@endpush
