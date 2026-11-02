<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\Order;
use App\Models\Payment;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class BillingService
{
    public function generateBillForOrder(Order $order, int $cashierId, float $taxRate = 14.00, float $serviceRate = 10.00): Bill
    {
        return DB::transaction(function () use ($order, $cashierId, $taxRate, $serviceRate) {
            if ($order->bill) {
                return $order->bill;
            }

            if (in_array($order->status, ['draft', 'cancelled'])) {
                throw new Exception("Cannot generate a bill for an order with status: {$order->status}.");
            }

            $subtotal = (float) $order->total_amount; // already includes item offers
            $taxAmount = round($subtotal * ($taxRate / 100), 2);

            // Service fee applies to dine-in orders only
            $serviceAmount = 0.00;
            $appliedServiceRate = 0.00;
            if ($order->order_type === 'dine_in') {
                $appliedServiceRate = $serviceRate;
                $serviceAmount = round($subtotal * ($serviceRate / 100), 2);
            }

            $totalAmount = round($subtotal + $taxAmount + $serviceAmount, 2);

            $bill = Bill::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'order_id'       => $order->id,
                'user_id'        => $cashierId,
                'table_id'       => $order->table_id,
                'subtotal'       => $subtotal,
                'tax_rate'       => $taxRate,
                'tax_amount'     => $taxAmount,
                'service_rate'   => $appliedServiceRate,
                'service_amount' => $serviceAmount,
                'total_amount'   => $totalAmount,
                'status'         => 'unpaid',
            ]);

            return $bill->load(['order.items.menuItem', 'table', 'cashier']);
        });
    }

    public function recordPayment(Bill $bill, array $paymentData, int $cashierId): Payment
    {
        return DB::transaction(function () use ($bill, $paymentData, $cashierId) {
            // Locked because the Stripe redirect and webhook can arrive at the same moment.
            $bill = Bill::lockForUpdate()->findOrFail($bill->id);

            if ($bill->status === 'paid') {
                throw new Exception("This bill has already been fully paid.");
            }

            $remainingAmount = $bill->remaining_amount;
            $amountTendered = (float) $paymentData['amount_tendered'];

            if ($amountTendered < $remainingAmount && $paymentData['payment_method'] === 'card') {
                throw new Exception("Card payment must cover the exact required amount.");
            }

            if ($amountTendered < $remainingAmount) {
                throw new Exception("The tendered amount is less than the remaining balance (\$" . number_format($remainingAmount, 2) . ").");
            }

            $amountPaid = $remainingAmount;
            $changeReturned = round(max(0, $amountTendered - $amountPaid), 2);

            $payment = Payment::create([
                'bill_id'          => $bill->id,
                'user_id'          => $cashierId,
                'payment_method'   => $paymentData['payment_method'],
                'amount_tendered'  => $amountTendered,
                'amount_paid'      => $amountPaid,
                'change_returned'  => $changeReturned,
                'reference_number' => $paymentData['reference_number'] ?? null,
            ]);

            $bill->update(['status' => 'paid']);

            $order = $bill->order;
            $order->update(['status' => 'completed']);

            if ($order->table_id) {
                $hasOtherActiveOrders = Order::where('table_id', $order->table_id)
                    ->where('id', '!=', $order->id)
                    ->whereNotIn('status', ['completed', 'cancelled'])
                    ->exists();

                if (!$hasOtherActiveOrders) {
                    $order->table->update(['status' => 'available']);
                }
            }

            return $payment;
        });
    }

    /**
     * Format: INV-YYYYMMDD-NNNN, the sequence resets daily.
     */
    protected function generateInvoiceNumber(): string
    {
        $today = Carbon::now()->format('Ymd');
        $prefix = "INV-{$today}-";

        $latestBill = Bill::where('invoice_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        $nextSeq = 1;
        if ($latestBill) {
            $lastSeq = (int) str_replace($prefix, '', $latestBill->invoice_number);
            $nextSeq = $lastSeq + 1;
        }

        return $prefix . str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);
    }
}
