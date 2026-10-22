@extends('layouts.app')

@section('title', 'Kitchen Display System')
@section('page_title', 'Kitchen Display System (KDS)')

@section('content')
    <style>
        .order-card {
            border-top: 5px solid #0d6efd;
            transition: all 0.3s ease;
        }

        .order-card.status-prep {
            border-top-color: #ffc107;
        }

        .timer-badge.delay-warning {
            background-color: #ffc107;
            color: #000;
        }

        .timer-badge.delay-danger {
            background-color: #dc3545;
            color: #fff;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }
    </style>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-fire text-danger me-2"></i>Live Kitchen Queue</h5>
            <small class="text-muted">Auto-refreshes every 15 seconds. Keep this screen open on the kitchen display.</small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-secondary px-3 py-2 fs-6" id="totalOrdersCount">{{ $orders->count() }} Active Orders</span>
            <button class="btn btn-outline-primary btn-sm" onclick="window.location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
    </div>

    <div class="row g-3" id="kitchenGrid">
        @if ($orders->count() > 0)
            @foreach ($orders as $ord)
                @php
                    $maxPrepTime = $ord->items->max(fn($it) => $it->menuItem->preparation_time ?? 15) ?? 15;
                @endphp
                <div class="col-xl-3 col-lg-4 col-md-6" id="order-card-{{ $ord->id }}">
                    <div
                        class="card h-100 shadow-sm border-0 order-card {{ $ord->status === 'in_preparation' ? 'status-prep' : '' }}">
                        <div
                            class="card-header bg-white py-2 d-flex justify-content-between align-items-center border-bottom">
                            <div>
                                <strong class="fs-5 text-dark">{{ $ord->order_number }}</strong>
                                <div class="small text-muted">
                                    @if ($ord->order_type === 'dine_in')
                                        <span class="badge bg-primary">Table {{ $ord->table->table_number ?? 'N/A' }}</span>
                                    @else
                                        <span class="badge bg-secondary">Takeaway</span>
                                    @endif
                                    <span class="ms-1">{{ $ord->user->name }}</span>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-dark timer-badge" id="timer-{{ $ord->id }}"
                                    data-created="{{ $ord->created_at->toISOString() }}" data-target="{{ $maxPrepTime }}">
                                    00:00
                                </span>
                                <div class="small text-muted" style="font-size: 0.7rem;">Target: {{ $maxPrepTime }}m</div>
                            </div>
                        </div>

                        <div class="card-body p-3">
                            <ul class="list-unstyled mb-0">
                                @foreach ($ord->items as $item)
                                    <li class="border-bottom py-2">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <span class="fw-bold text-dark fs-6">{{ $item->quantity }}x
                                                {{ $item->menuItem->name }}</span>
                                        </div>
                                        @if ($item->special_instructions)
                                            <div
                                                class="badge bg-danger-subtle text-danger border border-danger-subtle mt-1 text-wrap text-start">
                                                <i
                                                    class="bi bi-exclamation-triangle-fill me-1"></i>{{ $item->special_instructions }}
                                            </div>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>

                            @if ($ord->notes)
                                <div class="alert alert-warning py-1 px-2 mt-2 mb-0 small">
                                    <strong>Note:</strong> {{ $ord->notes }}
                                </div>
                            @endif
                        </div>

                        <div class="card-footer bg-white border-top p-2 text-center">
                            @if ($ord->status === 'sent_to_kitchen')
                                <button class="btn btn-warning w-100 fw-bold btn-sm"
                                    onclick="advanceOrderStatus({{ $ord->id }}, 'in_preparation')">
                                    <i class="bi bi-play-circle me-1"></i> Start Cooking
                                </button>
                            @elseif($ord->status === 'in_preparation')
                                <button class="btn btn-success w-100 fw-bold btn-sm"
                                    onclick="advanceOrderStatus({{ $ord->id }}, 'ready')">
                                    <i class="bi bi-check-circle-fill me-1"></i> Mark as Ready
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="col-12" id="noOrdersMessage">
                <div class="card shadow-sm border-0 text-center py-5 text-muted">
                    <i class="bi bi-check2-circle text-success" style="font-size: 3rem;"></i>
                    <h5 class="mt-2 fw-bold text-dark">Kitchen Queue is Empty!</h5>
                    <p class="mb-0">All incoming orders have been prepared and served.</p>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        function updateTimers() {
            const timers = document.querySelectorAll('.timer-badge');
            const now = new Date();

            timers.forEach(badge => {
                const createdAt = new Date(badge.getAttribute('data-created'));
                const targetMins = parseInt(badge.getAttribute('data-target')) || 15;
                const diffSeconds = Math.floor((now - createdAt) / 1000);

                const mins = Math.floor(diffSeconds / 60);
                const secs = diffSeconds % 60;
                badge.innerText = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;

                badge.classList.remove('delay-warning', 'delay-danger');
                if (mins >= targetMins) {
                    badge.classList.add('delay-danger');
                } else if (mins >= (targetMins * 0.75)) {
                    badge.classList.add('delay-warning');
                }
            });
        }

        setInterval(updateTimers, 1000);
        updateTimers();

        function advanceOrderStatus(orderId, newStatus) {
            fetch(`/kitchen/orders/${orderId}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        status: newStatus
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (newStatus === 'ready') {
                            const card = document.getElementById(`order-card-${orderId}`);
                            if (card) {
                                card.style.opacity = '0';
                                setTimeout(() => {
                                    card.remove();
                                    checkEmptyQueue();
                                }, 300);
                            }
                        } else {
                            window.location.reload();
                        }
                    } else {
                        alert(data.message || 'Error updating order status');
                    }
                })
                .catch(err => console.error(err));
        }

        function checkEmptyQueue() {
            const grid = document.getElementById('kitchenGrid');
            if (grid.querySelectorAll('.order-card').length === 0) {
                window.location.reload();
            }
        }

        setInterval(() => {
            fetch('{{ route('kitchen.orders.data') }}')
                .then(res => res.json())
                .then(orders => {
                    const currentCount = document.querySelectorAll('.order-card').length;
                    if (orders.length !== currentCount) {
                        window.location.reload();
                    }
                });
        }, 15000);
    </script>
@endpush
@TODO: should finsh kitchen UI first in saturday