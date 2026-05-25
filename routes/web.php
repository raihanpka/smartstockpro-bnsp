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
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    Route::post('products/import', [\App\Http\Controllers\Web\ProductController::class, 'import'])->name('products.import');
    Route::post('products/export', [\App\Http\Controllers\Web\ProductController::class, 'export'])->name('products.export');
    Route::resource('products', \App\Http\Controllers\Web\ProductController::class);
    Route::resource('transactions', \App\Http\Controllers\Web\TransactionController::class)->only(['index', 'create', 'store']);
    Route::resource('transfers', \App\Http\Controllers\Web\TransferController::class)->except(['show', 'destroy']);
    Route::resource('categories', \App\Http\Controllers\Web\CategoryController::class)->except(['show', 'destroy', 'edit', 'update']);
    Route::resource('warehouses', \App\Http\Controllers\Web\WarehouseController::class)->except(['show', 'destroy', 'edit', 'update']);
    Route::resource('suppliers', \App\Http\Controllers\Web\SupplierController::class)->except(['show', 'destroy', 'edit', 'update']);
    
    Route::get('/system-logs', [\App\Http\Controllers\Web\SystemLogController::class, 'index'])->name('system-logs');
});

require __DIR__.'/auth.php';
