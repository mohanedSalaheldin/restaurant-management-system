<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Order;
use App\Services\BillingService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function __construct(
        protected BillingService $billingService
    ) {}

    public function index(Request $request): View
    {
        $pendingOrders = Order::with(['table', 'user', 'items.menuItem'])
            ->whereIn('status', ['ready', 'served'])
            ->whereDoesntHave('bill')
            ->latest()
            ->get();

        $bills = Bill::with(['order.table', 'cashier', 'payments'])
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('billing.index', compact('pendingOrders', 'bills'));
    }

    public function createBill(Order $order): RedirectResponse
    {
        try {
            $bill = $this->billingService->generateBillForOrder(
                $order,
                auth()->id()
            );

            return redirect()->route('billing.show', $bill)->with('success', "Invoice #{$bill->invoice_number} generated successfully.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function show(Bill $bill): View
    {
        $bill->load(['order.items.menuItem', 'table', 'cashier', 'payments']);
        return view('billing.show', compact('bill'));
    }

    public function processPayment(Request $request, Bill $bill): RedirectResponse
    {
        $validated = $request->validate([
            'payment_method'   => 'required|in:cash,card',
            'amount_tendered'  => 'required|numeric|min:0.01',
            'reference_number' => 'nullable|string|max:100',
        ]);

        try {
            $payment = $this->billingService->recordPayment(
                $bill,
                $validated,
                auth()->id()
            );

            return redirect()->route('billing.receipt', $bill)->with('success', "Payment of \${$payment->amount_paid} recorded. Change: \${$payment->change_returned}");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function printReceipt(Bill $bill): View
    {
        $bill->load(['order.items.menuItem', 'table', 'cashier', 'payments']);
        return view('billing.receipt', compact('bill'));
    }
}
