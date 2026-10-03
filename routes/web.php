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

// المسارات التي تتطلب تسجيل الدخول لجميع المستخدمين
Route::middleware(['auth'])->group(function () {

    // مسارات الملف الشخصي المشتركة لجميع الأدوار
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // مسار استعراض المنيو
    Route::get('/menu-view', [\App\Http\Controllers\MenuDisplayController::class, 'index'])->name('menu.display');

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
});

require __DIR__ . '/auth.php';
