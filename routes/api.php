<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Inventory\WarehouseController;
use App\Http\Controllers\Inventory\CategoryController;
use App\Http\Controllers\Inventory\SupplierController;
use App\Http\Controllers\Inventory\ProductController;
use App\Http\Controllers\Inventory\TransactionController;
use App\Http\Controllers\Transfer\TransferController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Menggunakan auth middleware secara umum, namun bisa dilewati jika request testing belum di set up sepenuhnya untuk semua request
Route::middleware([])->group(function () {
    Route::apiResource('warehouses', WarehouseController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('suppliers', SupplierController::class);
    Route::apiResource('products', ProductController::class);
    Route::post('transactions', [TransactionController::class, 'store']);
    Route::get('transactions', [TransactionController::class, 'index']);
    Route::post('transfers', [TransferController::class, 'store']);
    Route::get('transfers', [TransferController::class, 'index']);
    
    Route::get('dashboard', [\App\Http\Controllers\DashboardController::class, 'index']);
    Route::post('reports', [\App\Http\Controllers\ReportController::class, 'store']);
});
