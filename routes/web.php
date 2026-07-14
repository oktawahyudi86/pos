<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AddonController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VariantGroupController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\CashierOrderController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OnlineOrderController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\TenantController as SuperAdminTenantController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $user = request()->user();

    if ($user->hasRole('Super Admin')) {
        return redirect()->route('super-admin.dashboard');
    }

    if ($user->status === 'pending' || $user->tenant?->status === 'pending') {
        return redirect()->route('pending-approval');
    }

    if ($user->hasRole('Admin')) {
        return redirect()->route('admin.dashboard');
    }

    if ($user->hasRole('Kasir')) {
        return redirect()->route('cashier.index');
    }

    abort(403);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::view('/pending-approval', 'auth.pending-approval')
    ->middleware(['auth', 'verified'])
    ->name('pending-approval');

Route::middleware(['auth', 'verified', 'role:Super Admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/dashboard', SuperAdminDashboardController::class)->name('dashboard');
    Route::get('/tenants', [SuperAdminTenantController::class, 'index'])->name('tenants.index');
    Route::get('/tenants/{tenant}', [SuperAdminTenantController::class, 'show'])->name('tenants.show');
    Route::patch('/tenants/{tenant}/activate', [SuperAdminTenantController::class, 'activate'])->name('tenants.activate');
    Route::patch('/tenants/{tenant}/suspend', [SuperAdminTenantController::class, 'suspend'])->name('tenants.suspend');
    Route::patch('/tenants/{tenant}/users/{user}', [SuperAdminTenantController::class, 'updateUser'])->name('tenants.users.update');
    Route::post('/tenants/{tenant}/users/{user}/reset-password', [SuperAdminTenantController::class, 'sendUserPasswordReset'])->name('tenants.users.reset-password');
});

Route::middleware(['auth', 'verified', 'role:Admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::resource('products', ProductController::class);
    Route::resource('users', UserController::class)->except(['show']);
    Route::resource('categories', CategoryController::class);
    Route::resource('variant-groups', VariantGroupController::class);
    Route::resource('addons', AddonController::class);
    Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
});

Route::middleware(['auth', 'verified', 'role:Kasir'])->prefix('kasir')->name('cashier.')->group(function () {
    Route::get('/', [CashierController::class, 'index'])->name('index');
    Route::post('/cart', [CashierController::class, 'storeCart'])->name('cart.store');
    Route::patch('/cart/{key}', [CashierController::class, 'updateCart'])->name('cart.update');
    Route::patch('/cart/{key}/edit', [CashierController::class, 'editCart'])->name('cart.edit');
    Route::patch('/discount', [CashierController::class, 'updateDiscount'])->name('discount.update');
    Route::patch('/payment-method', [CashierController::class, 'updatePaymentMethod'])->name('payment-method.update');
    Route::post('/checkout', [CashierController::class, 'checkout'])->name('checkout');
    Route::delete('/cart/{key}', [CashierController::class, 'destroyCart'])->name('cart.destroy');
    Route::delete('/cart', [CashierController::class, 'clearCart'])->name('cart.clear');

    Route::get('/orderan', [CashierOrderController::class, 'index'])->name('orders.index');
    Route::patch('/orderan/{order}/payment-reminder', [CashierOrderController::class, 'paymentReminder'])->name('orders.payment-reminder');
    Route::patch('/orderan/{order}/process', [CashierOrderController::class, 'process'])->name('orders.process');
    Route::patch('/orderan/{order}/ship', [CashierOrderController::class, 'ship'])->name('orders.ship');
    Route::patch('/orderan/{order}/finish', [CashierOrderController::class, 'finish'])->name('orders.finish');
    Route::patch('/orderan/{order}/cancel', [CashierOrderController::class, 'cancel'])->name('orders.cancel');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/transaksi', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transaksi/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::get('/transaksi/{transaction}/receipt', [TransactionController::class, 'receipt'])->name('transactions.receipt');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/pelanggan', [CustomerController::class, 'index'])->name('customers.index');
    Route::post('/pelanggan/{customer}/deactivate', [CustomerController::class, 'deactivate'])->name('customers.deactivate');
    Route::post('/pelanggan/{customer}/activate', [CustomerController::class, 'activate'])->name('customers.activate');
    Route::post('/pelanggan/{customer}/reset-password', [CustomerController::class, 'sendPasswordReset'])->name('customers.reset-password');
});

Route::get('/r/{code}', [TransactionController::class, 'publicReceiptByCode'])->name('transactions.receipt.short');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::prefix('{tenant:slug}')->name('online-orders.')->group(function () {
    Route::get('/', [OnlineOrderController::class, 'index'])->name('catalog');
    Route::get('/profil', [CustomerAuthController::class, 'profile'])->name('profile');
    Route::get('/auth', [CustomerAuthController::class, 'show'])->name('auth');
    Route::get('/auth/register', [CustomerAuthController::class, 'show'])->middleware('guest')->name('auth.register')->defaults('tab', 'register');
    Route::post('/auth/login', [CustomerAuthController::class, 'login'])->middleware('guest')->name('auth.login');
    Route::post('/auth/register', [CustomerAuthController::class, 'register'])->middleware('guest')->name('auth.register.store');
    Route::post('/auth/logout', [CustomerAuthController::class, 'logout'])->middleware('auth')->name('auth.logout');
    Route::get('/produk/{product}', [OnlineOrderController::class, 'productDetail'])->name('product.detail');
    Route::post('/cart', [OnlineOrderController::class, 'storeCart'])->name('cart.store');
    Route::patch('/cart/{key}', [OnlineOrderController::class, 'updateCart'])->name('cart.update');
    Route::delete('/cart/{key}', [OnlineOrderController::class, 'destroyCart'])->name('cart.destroy');
    Route::get('/address', [OnlineOrderController::class, 'addressConfirmation'])->name('address');
    Route::get('/checkout', [OnlineOrderController::class, 'review'])->name('checkout.form');
    Route::get('/reverse-geocode', [OnlineOrderController::class, 'reverseGeocode'])->name('reverse-geocode');
    Route::get('/delivery-coverage', [OnlineOrderController::class, 'deliveryCoverage'])->name('delivery-coverage');
    Route::get('/geocode-search', [OnlineOrderController::class, 'geocodeSearch'])->name('geocode-search');
    Route::post('/checkout', [OnlineOrderController::class, 'checkout'])->name('checkout');
    Route::get('/pesanan/{order}', [OnlineOrderController::class, 'success'])->name('success');
    Route::get('/cek-pesanan', [OnlineOrderController::class, 'track'])->name('track');
});
