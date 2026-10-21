<?php

use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route(Auth::user()->role->redirectRoute());
    }

    return redirect()->route('login');
});

Route::get('/table/{table_number}/{token}', [\App\Http\Controllers\PublicTableController::class, 'show'])->name('table.qr.view');

Route::middleware(['auth'])->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/menu-view', [\App\Http\Controllers\MenuDisplayController::class, 'index'])->name('menu.display');

    Route::middleware(['role:admin,waiter,cashier'])->group(function () {
        Route::resource('orders', \App\Http\Controllers\OrderController::class)->except(['destroy', 'edit', 'update']);
        Route::patch('orders/{order}/status', [\App\Http\Controllers\OrderController::class, 'updateStatus'])->name('orders.update-status');
    });

    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');


        Route::resource('sections', \App\Http\Controllers\Admin\SectionController::class);
        Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class);
        Route::resource('subcategories', \App\Http\Controllers\Admin\SubcategoryController::class);

        Route::resource('menu-items', \App\Http\Controllers\Admin\MenuItemController::class);

        // JSON endpoints for the cascading dropdowns on the menu item form
        Route::get('sections/{section}/categories-data', [\App\Http\Controllers\Admin\MenuItemController::class, 'getCategoriesBySection'])->name('sections.categories.data');
        Route::get('categories/{category}/subcategories-data', [\App\Http\Controllers\Admin\MenuItemController::class, 'getSubcategoriesByCategory'])->name('categories.subcategories.data');
        Route::resource('tables', \App\Http\Controllers\Admin\TableController::class);
        Route::post('tables/{table}/regenerate-qr', [\App\Http\Controllers\Admin\TableController::class, 'regenerateQr'])->name('tables.regenerate.qr');
        Route::resource('offers', \App\Http\Controllers\Admin\OfferController::class);
        Route::resource('users', StaffController::class)->only(['index', 'create', 'store']);
    });

    Route::middleware(['role:waiter'])->prefix('waiter')->name('waiter.')->group(function () {
        Route::get('/dashboard', function () {
            return view('waiter.dashboard');
        })->name('dashboard');
    });

    Route::middleware(['role:cashier'])->prefix('cashier')->name('cashier.')->group(function () {
        Route::get('/dashboard', function () {
            return view('cashier.dashboard');
        })->name('dashboard');
    });

    Route::middleware(['role:kitchen_staff'])->prefix('kitchen')->name('kitchen.')->group(function () {
        Route::get('/view', function () {
            return view('kitchen.view');
        })->name('view');
    });
    Route::middleware(['role:admin,kitchen_staff'])->group(function () {
        Route::get('/kitchen/kds', [\App\Http\Controllers\KitchenController::class, 'index'])->name('kitchen.kds');
        Route::get('/kitchen/orders-data', [\App\Http\Controllers\KitchenController::class, 'fetchActiveOrders'])->name('kitchen.orders.data');
        Route::patch('/kitchen/orders/{order}/status', [\App\Http\Controllers\KitchenController::class, 'updateStatus'])->name('kitchen.order.status');
    });
});

require __DIR__ . '/auth.php';
