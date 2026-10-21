<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function createOrder(array $orderData, array $itemsData, int $userId): Order
    {
        return DB::transaction(function () use ($orderData, $itemsData, $userId) {
            if ($orderData['order_type'] === 'dine_in') {
                $table = Table::findOrFail($orderData['table_id']);

                $table->update(['status' => 'occupied']);
            }

            $orderNumber = $this->generateOrderNumber();

            $order = Order::create([
                'order_number'    => $orderNumber,
                'user_id'         => $userId,
                'table_id'        => $orderData['table_id'] ?? null,
                'order_type'      => $orderData['order_type'],
                'guest_count'     => $orderData['guest_count'] ?? 1,
                'status'          => $orderData['status'] ?? 'draft',
                'notes'           => $orderData['notes'] ?? null,
                'subtotal'        => 0,
                'discount_amount' => 0,
                'total_amount'    => 0,
            ]);

            $this->syncOrderItems($order, $itemsData);

            return $order->fresh(['items.menuItem', 'table', 'user']);
        });
    }

    public function updateOrder(Order $order, array $orderData, array $itemsData): Order
    {
        return DB::transaction(function () use ($order, $orderData, $itemsData) {
            if (in_array($order->status, ['completed', 'cancelled'])) {
                throw new Exception("Cannot update an order that is already {$order->status}.");
            }

            $order->update([
                'guest_count' => $orderData['guest_count'] ?? $order->guest_count,
                'notes'       => $orderData['notes'] ?? $order->notes,
            ]);

            $order->items()->delete();
            $this->syncOrderItems($order, $itemsData);

            return $order->fresh(['items.menuItem', 'table']);
        });
    }

    protected function syncOrderItems(Order $order, array $itemsData): void
    {
        $subtotal = 0;
        $totalDiscount = 0;

        foreach ($itemsData as $itemInput) {
            if (empty($itemInput['quantity']) || $itemInput['quantity'] <= 0) {
                continue;
            }

            $menuItem = MenuItem::with('offers')->findOrFail($itemInput['menu_item_id']);

            $qty = (int) $itemInput['quantity'];
            $unitPrice = (float) $menuItem->price;

            $discountedPrice = $menuItem->final_price;
            $lineDiscount = ($unitPrice - $discountedPrice) * $qty;
            $lineSubtotal = $discountedPrice * $qty;

            OrderItem::create([
                'order_id'             => $order->id,
                'menu_item_id'         => $menuItem->id,
                'quantity'             => $qty,
                'unit_price'           => $unitPrice,
                'discounted_price'     => $discountedPrice,
                'subtotal'             => $lineSubtotal,
                'special_instructions' => $itemInput['special_instructions'] ?? null,
                'status'               => 'pending',
            ]);

            $subtotal += ($unitPrice * $qty);
            $totalDiscount += $lineDiscount;
        }

        $order->update([
            'subtotal'        => $subtotal,
            'discount_amount' => $totalDiscount,
            'total_amount'    => ($subtotal - $totalDiscount),
        ]);
    }

    public function updateOrderStatus(Order $order, string $newStatus): Order
    {
        return DB::transaction(function () use ($order, $newStatus) {
            $allowedTransitions = [
                'draft'           => ['sent_to_kitchen', 'cancelled'],
                'sent_to_kitchen' => ['in_preparation', 'cancelled'],
                'in_preparation'  => ['ready', 'cancelled'],
                'ready'           => ['served', 'cancelled'],
                'served'          => ['completed', 'cancelled'],
                'completed'       => [],
                'cancelled'       => [],
            ];

            if (!isset($allowedTransitions[$order->status]) || !in_array($newStatus, $allowedTransitions[$order->status])) {
                throw new Exception("Invalid status transition from {$order->status} to {$newStatus}.");
            }

            $order->update(['status' => $newStatus]);

            // Free the table only when it has no other active orders
            if (in_array($newStatus, ['completed', 'cancelled']) && $order->table_id) {
                $hasActiveOrders = Order::where('table_id', $order->table_id)
                    ->where('id', '!=', $order->id)
                    ->whereNotIn('status', ['completed', 'cancelled'])
                    ->exists();

                if (!$hasActiveOrders) {
                    $order->table->update(['status' => 'available']);
                }
            }

            return $order;
        });
    }

    /**
     * Format: ORD-YYYYMMDD-NNNN, the sequence resets daily.
     */
    protected function generateOrderNumber(): string
    {
        $today = Carbon::now()->format('Ymd');
        $prefix = "ORD-{$today}-";

        $latestOrder = Order::where('order_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = 1;
        if ($latestOrder) {
            $lastSeq = (int) str_replace($prefix, '', $latestOrder->order_number);
            $nextNumber = $lastSeq + 1;
        }

        return $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
