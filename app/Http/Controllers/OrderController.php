<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Table;
use App\Services\OrderService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function index(Request $request): View
    {
        $orders = Order::with(['table', 'user', 'items.menuItem'])
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($request->filled('order_type'), fn($q) => $q->where('order_type', $request->order_type))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('orders.index', compact('orders'));
    }

    public function create(): View
    {
        $tables = Table::where('status', 'available')->orderBy('table_number')->get();
        $categories = Category::where('status', true)->orderBy('display_order')->get();

        $menuItems = MenuItem::with('offers')
            ->where('availability', 'available')
            ->orderBy('name')
            ->get();

        return view('orders.create', compact('tables', 'categories', 'menuItems'));
    }

    public function store(OrderRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();
            $itemsData = $validated['items'];
            unset($validated['items']);

            $order = $this->orderService->createOrder(
                $validated,
                $itemsData,
                auth()->id()
            );

            $msg = $order->status === 'sent_to_kitchen'
                ? "Order #{$order->order_number} has been sent to the kitchen!"
                : "Order #{$order->order_number} saved as draft.";

            return redirect()->route('orders.show', $order)->with('success', $msg);
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(Order $order): View
    {
        $order->load(['table', 'user', 'items.menuItem']);
        return view('orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:draft,sent_to_kitchen,in_preparation,ready,served,completed,cancelled'
        ]);

        try {
            $this->orderService->updateOrderStatus($order, $request->status);
            return back()->with('success', "Order #{$order->order_number} status updated to " . ucfirst(str_replace('_', ' ', $request->status)));
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
