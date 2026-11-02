<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Http\Resources\Api\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderApiController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate(['status' => 'nullable|string']);

        $orders = Order::with(['table', 'items.menuItem'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(10);

        return response()->json([
            'status'     => true,
            'data'       => OrderResource::collection($orders),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
                'total'        => $orders->total(),
            ],
        ]);
    }

    public function store(OrderRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $itemsData = $validated['items'];
            unset($validated['items']);

            $order = $this->orderService->createOrder(
                $validated,
                $itemsData,
                $request->user()->id
            );

            return response()->json([
                'status'  => true,
                'message' => 'Order created successfully.',
                'data'    => new OrderResource($order),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(Order $order): JsonResponse
    {
        $order->load(['table', 'items.menuItem']);

        return response()->json([
            'status' => true,
            'data'   => new OrderResource($order),
        ]);
    }
}
