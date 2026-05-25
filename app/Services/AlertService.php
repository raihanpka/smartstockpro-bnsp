<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class AlertService
{
    public static function checkAndResolve(Product $product)
    {
        if ($product->stock >= $product->min_stock) {
            // Logika untuk meresolve alert jika ada
            // Misalnya update status notifikasi di DB
            Log::info("Stock for product {$product->sku} is now sufficient.");
        }
    }

    public static function checkAndAlert(Product $product)
    {
        if ($product->stock < $product->min_stock) {
            Log::warning("Stock for product {$product->sku} is below minimum ({$product->stock} < {$product->min_stock}).");
            
            // Mengirim notifikasi ke admin/manajer. Dalam real app, kirim ke User dengan role admin/manager.
            $admins = \App\Models\User::whereIn('role', ['admin', 'manager'])->get();
            Notification::send($admins, new \App\Notifications\LowStockAlert($product));
        }
    }
}
