<?php

use App\Http\Controllers\Admin\StaffController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check() && Auth::user()?->role) {
        return redirect()->route(Auth::user()->role->redirectRoute());
    }

    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {

    // 1.Admin
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');

        Route::resource('users', StaffController::class)->only(['index', 'create', 'store']);
    });

    // 2.Waiter
    Route::middleware(['role:waiter'])->prefix('waiter')->name('waiter.')->group(function () {
        Route::get('/dashboard', function () {
            return view('waiter.dashboard');
        })->name('dashboard');
    });

    // 3.Cashier
    Route::middleware(['role:cashier'])->prefix('cashier')->name('cashier.')->group(function () {
        Route::get('/dashboard', function () {
            return view('cashier.dashboard');
        })->name('dashboard');
    });

    // 4.Kitchen Staff
    Route::middleware(['role:kitchen_staff'])->prefix('kitchen')->name('kitchen.')->group(function () {
        Route::get('/view', function () {
            return view('kitchen.view');
        })->name('view');
    });
});

require __DIR__.'/auth.php';
