@extends('layouts.app')

@section('title', 'Invoice ' . $bill->invoice_number)
@section('page_title', 'Invoice: ' . $bill->invoice_number)

@section('content')
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 fw-bold">Order Breakdown</h6>
                        <small class="text-muted">Order: {{ $bill->order->order_number }}</small>
                    </div>
                    <div>
                        @if ($bill->status === 'paid')
                            <span class="badge bg-success fs-6">PAID</span>
                        @else
                            <span class="badge bg-danger fs-6">UNPAID</span>
                        @endif
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Unit Price</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bill->order->items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $item->menuItem->name }}</div>
                                        @if ($item->special_instructions)
                                            <small class="text-muted">{{ $item->special_instructions }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>${{ number_format($item->discounted_price, 2) }}</td>
                                    <td class="text-end fw-semibold">${{ number_format($item->subtotal, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="card-footer bg-white border-top p-3">
                    <div class="d-flex justify-content-between text-muted mb-1">
                        <span>Subtotal (After Offers):</span>
                        <span>${{ number_format($bill->subtotal, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between text-muted mb-1">
                        <span>VAT ({{ (int) $bill->tax_rate }}%):</span>
                        <span>+${{ number_format($bill->tax_amount, 2) }}</span>
                    </div>
                    @if ($bill->service_amount > 0)
                        <div class="d-flex justify-content-between text-muted mb-1">
                            <span>Service Charge ({{ (int) $bill->service_rate }}%):</span>
                            <span>+${{ number_format($bill->service_amount, 2) }}</span>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between fw-bold fs-4 text-dark border-top pt-2">
                        <span>Grand Total:</span>
                        <span class="text-success">${{ number_format($bill->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            @if ($bill->status === 'unpaid')
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-credit-card me-1"></i> Process Payment</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('billing.pay', $bill) }}" method="POST" id="paymentForm">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Payment Method</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" id="payCash"
                                            value="cash" checked onchange="handleMethodChange()">
                                        <label class="form-check-label" for="payCash"><i class="bi bi-cash me-1"></i>
                                            Cash</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" id="payCard"
                                            value="card" onchange="handleMethodChange()">
                                        <label class="form-check-label" for="payCard"><i
                                                class="bi bi-credit-card me-1"></i> Card</label>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Amount Tendered ($)</label>
                                <input type="number" step="0.01" name="amount_tendered" id="amountTendered"
                                    class="form-control form-control-lg fw-bold" value="{{ $bill->remaining_amount }}"
                                    min="{{ $bill->remaining_amount }}" required oninput="calculateChange()">
                            </div>

                            <div class="mb-3 p-3 bg-light rounded text-center" id="changeBox">
                                <small class="text-muted d-block">Change to Return:</small>
                                <span class="fs-4 fw-bold text-primary" id="changeAmount">$0.00</span>
                            </div>

                            <div class="mb-3 d-none" id="referenceBox">
                                <label class="form-label small fw-semibold">Card Reference / Transaction ID</label>
                                <input type="text" name="reference_number" class="form-control"
                                    placeholder="e.g. AUTH-98214">
                            </div>

                            <button type="submit" class="btn btn-success btn-lg w-100">
                                <i class="bi bi-check-circle-fill me-1"></i> Complete & Print
                            </button>
                            <div class="mt-3">
                                <a href="{{ route('stripe.checkout', $bill) }}"
                                    class="btn btn-outline-primary w-100 py-2 fw-bold">
                                    <i class="bi bi-credit-card-2-front me-1"></i> Pay Online via Stripe Checkout
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            @else
                <div class="card shadow-sm border-0 text-center p-4">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 3.5rem;"></i>
                    <h5 class="fw-bold text-dark mt-2">Bill Fully Paid</h5>
                    <p class="text-muted small mb-3">Payment recorded and order is closed.</p>

                    @if ($bill->payments->count() > 0)
                        @php $p = $bill->payments->last(); @endphp
                        <div class="bg-light p-3 rounded text-start mb-3 small">
                            <div>Method: <strong>{{ ucfirst($p->payment_method) }}</strong></div>
                            <div>Tendered: <strong>${{ number_format($p->amount_tendered, 2) }}</strong></div>
                            <div>Paid: <strong>${{ number_format($p->amount_paid, 2) }}</strong></div>
                            <div>Change: <strong>${{ number_format($p->change_returned, 2) }}</strong></div>
                        </div>
                    @endif

                    <a href="{{ route('billing.receipt', $bill) }}" class="btn btn-dark">
                        <i class="bi bi-printer me-1"></i> View & Print Receipt
                    </a>
                </div>
            @endif
            <a href="{{ route('billing.index') }}" class="btn btn-outline-secondary w-100 mt-3">Back to Billing</a>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const totalRequired = {{ $bill->remaining_amount }};

        function calculateChange() {
            const tendered = parseFloat(document.getElementById('amountTendered').value) || 0;
            const change = Math.max(0, tendered - totalRequired);
            document.getElementById('changeAmount').innerText = `$${change.toFixed(2)}`;
        }

        function handleMethodChange() {
            const isCard = document.getElementById('payCard').checked;
            const refBox = document.getElementById('referenceBox');
            const tenderedInput = document.getElementById('amountTendered');
            const changeBox = document.getElementById('changeBox');

            if (isCard) {
                refBox.classList.remove('d-none');
                tenderedInput.value = totalRequired.toFixed(2);
                tenderedInput.setAttribute('readonly', 'readonly');
                changeBox.classList.add('d-none');
            } else {
                refBox.classList.add('d-none');
                tenderedInput.removeAttribute('readonly');
                changeBox.classList.remove('d-none');
            }
            calculateChange();
        }
    </script>
@endpush
