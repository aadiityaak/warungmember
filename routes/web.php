<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DepositController;
use App\Http\Controllers\Admin\KasirController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\OutletController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\RewardController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\ManifestController;
use App\Http\Controllers\Member\CartController;
use App\Http\Controllers\Member\DashboardController as MemberDashboardController;
use App\Http\Controllers\Member\DepositController as MemberDepositController;
use App\Http\Controllers\Member\NotificationController;
use App\Http\Controllers\Member\OrderController as MemberOrderController;
use App\Http\Controllers\Member\OutletController as MemberOutletController;
use App\Http\Controllers\Member\PointController;
use App\Http\Controllers\Member\ProductController as MemberProductController;
use App\Http\Controllers\Member\ProfileController;
use App\Http\Controllers\Member\RewardController as MemberRewardController;
use App\Http\Controllers\Member\VoucherController as MemberVoucherController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::get('manifest.webmanifest', ManifestController::class)->name('manifest');

Route::middleware(['auth', 'verified'])->group(function () {
    // Redirect /dashboard to appropriate dashboard based on role
    Route::get('dashboard', function () {
        return redirect()->route(
            auth()->user()->isAdmin() || auth()->user()->isKasir()
                ? 'admin.dashboard'
                : 'member.dashboard'
        );
    })->name('dashboard');

    // Member routes (mobile-only)
    Route::middleware('role:member')->prefix('member')->name('member.')->group(function () {
        Route::get('dashboard', MemberDashboardController::class)->name('dashboard');
        Route::get('points', PointController::class)->name('points');
        Route::get('rewards', [MemberRewardController::class, 'index'])->name('rewards');
        Route::post('rewards/{reward}/redeem', [MemberRewardController::class, 'redeem'])->name('rewards.redeem');
        Route::get('deposits', MemberDepositController::class)->name('deposits');
        Route::get('vouchers', MemberVoucherController::class)->name('vouchers');
        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications');
        Route::post('notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
        Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.readAll');
        Route::get('profile', [ProfileController::class, 'index'])->name('profile');
        Route::get('profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::get('terms', [ProfileController::class, 'terms'])->name('terms');
        Route::get('privacy', [ProfileController::class, 'privacy'])->name('privacy');
        Route::get('products', [MemberProductController::class, 'index'])->name('products.index');
        Route::get('orders/history', [MemberOrderController::class, 'history'])->name('orders.history');
        Route::resource('orders', MemberOrderController::class)->only(['index', 'store']);
        Route::get('outlets', [MemberOutletController::class, 'index'])->name('outlets.index');
        Route::get('outlets/{outlet}', [MemberOutletController::class, 'show'])->name('outlets.show');
        Route::post('outlets/select', [MemberOutletController::class, 'select'])->name('outlets.select');
        Route::get('cart', [CartController::class, 'index'])->name('cart.index');
        Route::post('cart/sync', [CartController::class, 'sync'])->name('cart.sync');
    });

    // Admin routes (desktop - for admin & kasir)
    Route::middleware('role:admin,kasir')->prefix('admin')->name('admin.')->group(function () {
        Route::get('dashboard', AdminDashboardController::class)->name('dashboard');
        Route::resource('members', MemberController::class);
        Route::resource('rewards', RewardController::class);
        Route::get('deposits', [DepositController::class, 'index'])->name('deposits.index');
        Route::post('deposits', [DepositController::class, 'store'])->name('deposits.store');
        Route::get('deposits/{member}', [DepositController::class, 'history'])->name('deposits.history');
        Route::resource('vouchers', VoucherController::class)->only(['index', 'create', 'store', 'destroy']);
        Route::resource('products', ProductController::class);
        Route::resource('outlets', OutletController::class);
        Route::post('outlets/upload', [OutletController::class, 'upload'])->name('outlets.upload');
        Route::resource('kasir', KasirController::class);
        Route::resource('orders', AdminOrderController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('orders/{order}/receipt', [AdminOrderController::class, 'receipt'])->name('orders.receipt');
    });
});

require __DIR__.'/settings.php';
