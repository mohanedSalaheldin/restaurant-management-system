<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\MenuItemController;
use App\Http\Controllers\Admin\OfferController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\SubcategoryController;
use App\Http\Controllers\Admin\TableController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\MenuDisplayController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicTableController;
use App\Http\Controllers\StripeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route(Auth::user()->role->redirectRoute());
    }

    return redirect()->route('login');
});

Route::get('table/{table_number}/{token}', [PublicTableController::class, 'show'])->name('table.qr.view');
Route::post('stripe/webhook', [StripeController::class, 'webhook'])->name('stripe.webhook');

Route::middleware('auth')->group(function () {
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('menu-view', [MenuDisplayController::class, 'index'])->name('menu.display');

    Route::middleware('role:admin,waiter,cashier')->group(function () {
        Route::resource('orders', OrderController::class)->except(['destroy', 'edit', 'update']);
        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    });

    Route::middleware('role:admin,cashier')->group(function () {
        Route::get('billing', [BillingController::class, 'index'])->name('billing.index');
        Route::post('billing/orders/{order}/generate', [BillingController::class, 'createBill'])->name('billing.generate');
        Route::get('billing/invoices/{bill}', [BillingController::class, 'show'])->name('billing.show');
        Route::post('billing/invoices/{bill}/pay', [BillingController::class, 'processPayment'])->name('billing.pay');
        Route::get('billing/invoices/{bill}/receipt', [BillingController::class, 'printReceipt'])->name('billing.receipt');

        Route::get('stripe/checkout/{bill}', [StripeController::class, 'checkout'])->name('stripe.checkout');
        Route::get('stripe/success', [StripeController::class, 'success'])->name('stripe.success');
    });

    Route::middleware('role:admin,kitchen_staff')->group(function () {
        Route::get('kitchen/kds', [KitchenController::class, 'index'])->name('kitchen.kds');
        Route::get('kitchen/orders-data', [KitchenController::class, 'fetchActiveOrders'])->name('kitchen.orders.data');
        Route::patch('kitchen/orders/{order}/status', [KitchenController::class, 'updateStatus'])->name('kitchen.order.status');
    });

    Route::middleware('role:kitchen_staff')->get('kitchen/view', fn () => view('kitchen.view'))->name('kitchen.view');
    Route::middleware('role:waiter')->get('waiter/dashboard', fn () => view('waiter.dashboard'))->name('waiter.dashboard');
    Route::middleware('role:cashier')->get('cashier/dashboard', fn () => view('cashier.dashboard'))->name('cashier.dashboard');

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('dashboard', fn () => view('admin.dashboard'))->name('dashboard');

        Route::resource('sections', SectionController::class);
        Route::resource('categories', CategoryController::class);
        Route::resource('subcategories', SubcategoryController::class);
        Route::resource('menu-items', MenuItemController::class);

        // JSON endpoints for the cascading dropdowns on the menu item form
        Route::get('sections/{section}/categories-data', [MenuItemController::class, 'getCategoriesBySection'])->name('sections.categories.data');
        Route::get('categories/{category}/subcategories-data', [MenuItemController::class, 'getSubcategoriesByCategory'])->name('categories.subcategories.data');

        Route::resource('tables', TableController::class);
        Route::post('tables/{table}/regenerate-qr', [TableController::class, 'regenerateQr'])->name('tables.regenerate.qr');

        Route::resource('offers', OfferController::class);
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::resource('users', StaffController::class)->only(['index', 'create', 'store']);
    });
});

require __DIR__.'/auth.php';
