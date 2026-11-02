<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $bill->invoice_number }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            background-color: #f2f2f2;
            margin: 0;
            padding: 20px;
        }

        .receipt-container {
            max-width: 340px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .text-center {
            text-align: center;
        }

        .text-end {
            text-align: right;
        }

        .fw-bold {
            font-weight: bold;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 13px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
            font-size: 13px;
        }

        .btn-print {
            display: block;
            width: 100%;
            padding: 10px;
            background: #0d6efd;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            margin-bottom: 15px;
        }

        .btn-back {
            display: block;
            width: 100%;
            padding: 8px;
            background: #6c757d;
            color: #fff;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            font-size: 13px;
        }

        @media print {
            body {
                background: transparent;
                padding: 0;
            }

            .receipt-container {
                box-shadow: none;
                padding: 0;
                width: 100%;
                max-width: 100%;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>

    <div class="receipt-container">
        <!-- Hidden when printing -->
        <div class="no-print">
            <button class="btn-print" onclick="window.print()">Print Receipt</button>
            <a href="{{ route('billing.index') }}" class="btn-back">Back to Terminal</a>
            <div style="margin-bottom: 15px;"></div>
        </div>

        <div class="text-center">
            <h3 style="margin: 0 0 5px 0;">DELICIOUS BISTRO</h3>
            <p style="margin: 0; font-size: 12px;">Alexandria Corniche, Alexandria</p>
            <p style="margin: 0; font-size: 12px;">Tel: +20 123 456 7890</p>
        </div>

        <div class="divider"></div>

        <div style="font-size: 12px;">
            <div>Invoice: <strong>{{ $bill->invoice_number }}</strong></div>
            <div>Order: <strong>{{ $bill->order->order_number }}</strong></div>
            <div>Date: {{ $bill->created_at->format('d/m/Y h:i A') }}</div>
            <div>Cashier: {{ $bill->cashier->name }}</div>
            <div>Type: {{ ucfirst($bill->order->order_type) }}
                {{ $bill->table ? '(Table ' . $bill->table->table_number . ')' : '' }}</div>
        </div>

        <div class="divider"></div>

        <div>
            @foreach ($bill->order->items as $item)
                <div class="item-row">
                    <span>{{ $item->quantity }}x {{ $item->menuItem->name }}</span>
                    <span>${{ number_format($item->subtotal, 2) }}</span>
                </div>
            @endforeach
        </div>

        <div class="divider"></div>

        <div class="total-row">
            <span>Subtotal:</span>
            <span>${{ number_format($bill->subtotal, 2) }}</span>
        </div>
        <div class="total-row">
            <span>VAT ({{ (int) $bill->tax_rate }}%):</span>
            <span>${{ number_format($bill->tax_amount, 2) }}</span>
        </div>
        @if ($bill->service_amount > 0)
            <div class="total-row">
                <span>Service ({{ (int) $bill->service_rate }}%):</span>
                <span>${{ number_format($bill->service_amount, 2) }}</span>
            </div>
        @endif

        <div class="divider"></div>

        <div class="total-row fw-bold" style="font-size: 16px;">
            <span>TOTAL:</span>
            <span>${{ number_format($bill->total_amount, 2) }}</span>
        </div>

        <div class="divider"></div>

        @if ($bill->payments->count() > 0)
            @php $payment = $bill->payments->last(); @endphp
            <div class="total-row">
                <span>Payment Method:</span>
                <span>{{ strtoupper($payment->payment_method) }}</span>
            </div>
            <div class="total-row">
                <span>Amount Tendered:</span>
                <span>${{ number_format($payment->amount_tendered, 2) }}</span>
            </div>
            <div class="total-row">
                <span>Change:</span>
                <span>${{ number_format($payment->change_returned, 2) }}</span>
            </div>
        @endif

        <div class="divider"></div>

        <div class="text-center" style="font-size: 12px; margin-top: 15px;">
            <p style="margin: 0;">Thank you for dining with us!</p>
            <p style="margin: 0;">Please visit us again.</p>
        </div>
    </div>

</body>

</html>
