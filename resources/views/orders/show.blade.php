@extends('layouts.app')

@section('title', 'Order ' . $order->order_number)
@section('page_title', 'Order Details: ' . $order->order_number)

@section('content')
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">Ordered Items</h6>
                    <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th>Unit Price</th>
                                <th>Qty</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->items as $it)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $it->menuItem->name }}</div>
                                        @if ($it->special_instructions)
                                            <small class="text-danger"><i
                                                    class="bi bi-info-circle me-1"></i>{{ $it->special_instructions }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($it->discounted_price < $it->unit_price)
                                            <span
                                                class="text-decoration-line-through text-muted small">${{ number_format($it->unit_price, 2) }}</span>
                                            <span
                                                class="text-danger fw-bold ms-1">${{ number_format($it->discounted_price, 2) }}</span>
                                        @else
                                            ${{ number_format($it->unit_price, 2) }}
                                        @endif
                                    </td>
                                    <td>{{ $it->quantity }}</td>
                                    <td class="fw-bold">${{ number_format($it->subtotal, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white border-top p-3 text-end">
                    <div class="text-muted small">Subtotal: <strong>${{ number_format($order->subtotal, 2) }}</strong></div>
                    @if ($order->discount_amount > 0)
                        <div class="text-danger small">Discount:
                            <strong>-${{ number_format($order->discount_amount, 2) }}</strong>
                        </div>
                    @endif
                    <div class="fs-5 fw-bold text-dark mt-1">Final Total: <span
                            class="text-success">${{ number_format($order->total_amount, 2) }}</span></div>
                </div>
            </div>
        </div>

        <!-- معلومات جانبية وتغيير الحالة -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold">Order Information</h6>
                </div>
                <div class="card-body">
                    <p class="mb-1 text-muted small">Created By: <strong
                            class="text-dark">{{ $order->user->name }}</strong></p>
                    <p class="mb-1 text-muted small">Type: <strong
                            class="text-dark">{{ ucfirst($order->order_type) }}</strong></p>
                    @if ($order->table)
                        <p class="mb-1 text-muted small">Table: <strong
                                class="text-dark">{{ $order->table->table_number }}</strong></p>
                    @endif
                    <p class="mb-1 text-muted small">Guests: <strong class="text-dark">{{ $order->guest_count }}</strong>
                    </p>
                    <p class="mb-3 text-muted small">Placed At: <strong
                            class="text-dark">{{ $order->created_at->format('M d, Y h:i A') }}</strong></p>

                    <!-- تغيير الحالة -->
                    @if (!in_array($order->status, ['completed', 'cancelled']))
                        <form action="{{ route('orders.update-status', $order) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <label class="form-label small fw-semibold">Advance Status:</label>
                            <div class="d-flex gap-2">
                                @if ($order->status === 'draft')
                                    <button type="submit" name="status" value="sent_to_kitchen"
                                        class="btn btn-success btn-sm w-100">Send to Kitchen</button>
                                @elseif($order->status === 'ready')
                                    <button type="submit" name="status" value="served"
                                        class="btn btn-primary btn-sm w-100">Mark as Served</button>
                                @endif
                                <button type="submit" name="status" value="cancelled"
                                    class="btn btn-outline-danger btn-sm"
                                    onclick="return confirm('Cancel this order?');">Cancel</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
            <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary w-100">Back to Orders</a>
        </div>
    </div>
@endsection
