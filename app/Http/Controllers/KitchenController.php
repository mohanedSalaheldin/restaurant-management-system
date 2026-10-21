<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KitchenController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function index(): View
    {
        $orders = Order::with(['table', 'user', 'items.menuItem'])
            ->whereIn('status', ['sent_to_kitchen', 'in_preparation'])
            ->orderBy('created_at', 'asc')
            ->get();

        return view('kitchen.index', compact('orders'));
    }

    /**
     * Polled by the KDS screen to detect new orders.
     */
    public function fetchActiveOrders(): JsonResponse
    {
        $orders = Order::with(['table', 'user', 'items.menuItem'])
            ->whereIn('status', ['sent_to_kitchen', 'in_preparation'])
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($orders);
    }

    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:in_preparation,ready'
        ]);

        try {
            $updatedOrder = $this->orderService->updateOrderStatus($order, $request->status);
            return response()->json([
                'success' => true,
                'message' => "Order #{$order->order_number} marked as {$request->status}",
                'order'   => $updatedOrder
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
