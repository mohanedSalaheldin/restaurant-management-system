<?php

use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// إعادة التوجيه التلقائي من الصفحة الرئيسية
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route(Auth::user()->role->redirectRoute());
    }

    return redirect()->route('login');
});

Route::get('/table/{table_number}/{token}', [\App\Http\Controllers\PublicTableController::class, 'show'])->name('table.qr.view');

// المسارات التي تتطلب تسجيل الدخول لجميع المستخدمين
Route::middleware(['auth'])->group(function () {

    // مسارات الملف الشخصي المشتركة لجميع الأدوار
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // مسار استعراض المنيو
    Route::get('/menu-view', [\App\Http\Controllers\MenuDisplayController::class, 'index'])->name('menu.display');
    // مسارات الطلبات المشتركة (Admin & Waiter)
    Route::resource('orders', \App\Http\Controllers\OrderController::class)->except(['destroy', 'edit', 'update']);
    Route::patch('orders/{order}/status', [\App\Http\Controllers\OrderController::class, 'updateStatus'])->name('orders.update-status');

    // 1. مسارات المسؤول (Admin)
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');


        Route::resource('sections', \App\Http\Controllers\Admin\SectionController::class);
        Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class);
        Route::resource('subcategories', \App\Http\Controllers\Admin\SubcategoryController::class);

        Route::resource('menu-items', \App\Http\Controllers\Admin\MenuItemController::class);

        // مسارات استعلام الـ Dropdowns التفاعلية
        Route::get('sections/{section}/categories-data', [\App\Http\Controllers\Admin\MenuItemController::class, 'getCategoriesBySection'])->name('sections.categories.data');
        Route::get('categories/{category}/subcategories-data', [\App\Http\Controllers\Admin\MenuItemController::class, 'getSubcategoriesByCategory'])->name('categories.subcategories.data');
        Route::resource('tables', \App\Http\Controllers\Admin\TableController::class);
        Route::post('tables/{table}/regenerate-qr', [\App\Http\Controllers\Admin\TableController::class, 'regenerateQr'])->name('tables.regenerate.qr');
        Route::resource('offers', \App\Http\Controllers\Admin\OfferController::class);
        Route::resource('users', StaffController::class)->only(['index', 'create', 'store']);
    });

    // 2. مسارات النادل (Waiter)
    Route::middleware(['role:waiter'])->prefix('waiter')->name('waiter.')->group(function () {
        Route::get('/dashboard', function () {
            return view('waiter.dashboard');
        })->name('dashboard');
    });

    // 3. مسارات الكاشير (Cashier)
    Route::middleware(['role:cashier'])->prefix('cashier')->name('cashier.')->group(function () {
        Route::get('/dashboard', function () {
            return view('cashier.dashboard');
        })->name('dashboard');
    });

    // 4. مسارات طاقم المطبخ (Kitchen Staff)
    Route::middleware(['role:kitchen_staff'])->prefix('kitchen')->name('kitchen.')->group(function () {
        Route::get('/view', function () {
            return view('kitchen.view');
        })->name('view');
    });
    // مسارات شاشة المطبخ KDS
    Route::middleware(['role:admin,kitchen_staff'])->group(function () {
        Route::get('/kitchen/kds', [\App\Http\Controllers\KitchenController::class, 'index'])->name('kitchen.kds');
        Route::get('/kitchen/orders-data', [\App\Http\Controllers\KitchenController::class, 'fetchActiveOrders'])->name('kitchen.orders.data');
        Route::patch('/kitchen/orders/{order}/status', [\App\Http\Controllers\KitchenController::class, 'updateStatus'])->name('kitchen.order.status');
    });
});

require __DIR__ . '/auth.php';
