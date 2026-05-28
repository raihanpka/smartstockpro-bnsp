<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', [\App\Http\Controllers\Web\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {

    // ── Profil (semua role) ──────────────────────────────────────
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── Produk (staff ke atas) ────────────────────────────────────
    Route::post('products/import', [\App\Http\Controllers\Web\ProductController::class, 'import'])->name('products.import')
        ->middleware('role:admin,manager,staff');
    Route::post('products/export', [\App\Http\Controllers\Web\ProductController::class, 'export'])->name('products.export');
    Route::resource('products', \App\Http\Controllers\Web\ProductController::class)
        ->middleware(['role:admin,manager,staff']);

    // ── Transaksi (staff ke atas) ─────────────────────────────────
    Route::resource('transactions', \App\Http\Controllers\Web\TransactionController::class)
        ->only(['index', 'create', 'store'])
        ->middleware('role:admin,manager,staff');

    // ── Transfer (viewer hanya lihat; staff bisa buat; admin/manager bisa approve) ──
    Route::resource('transfers', \App\Http\Controllers\Web\TransferController::class)
        ->except(['show', 'destroy']);

    // ── Master Data (hanya admin & manager) ──────────────────────
    Route::get('/catalog', function () {
        return redirect()->route('warehouses.index');
    })->name('catalog.index');

    Route::resource('categories', \App\Http\Controllers\Web\CategoryController::class)
        ->middleware('role:admin,manager');
    Route::resource('warehouses', \App\Http\Controllers\Web\WarehouseController::class)
        ->middleware('role:admin,manager');
    Route::resource('suppliers', \App\Http\Controllers\Web\SupplierController::class)
        ->middleware('role:admin,manager');

    // ── Notifikasi ────────────────────────────────────────────────────
    Route::post('notifications/{id}/read', [\App\Http\Controllers\Web\NotificationController::class, 'markRead'])
        ->name('notifications.markRead');
    Route::post('notifications/read-all', [\App\Http\Controllers\Web\NotificationController::class, 'markAllRead'])
        ->name('notifications.markAllRead');

    // ── Metrics API (untuk polling dashboard) ───────────────────────
    Route::get('/metrics', [\App\Http\Controllers\Web\MetricsController::class, 'index'])
        ->name('metrics');

    // ── Log Sistem (hanya admin) ──────────────────────────────────────
    Route::get('/system-logs', [\App\Http\Controllers\Web\SystemLogController::class, 'index'])
        ->name('system-logs')
        ->middleware('role:admin');
});

require __DIR__ . '/auth.php';
