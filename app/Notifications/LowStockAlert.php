<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Product;

class LowStockAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Product $product)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database']; // Untuk real aplikasi bisa ditambah 'mail'
    }

    public function toArray(object $notifiable): array
    {
        return [
            'product_id' => $this->product->id,
            'sku' => $this->product->sku,
            'message' => "Stok produk {$this->product->name} (SKU: {$this->product->sku}) berada di bawah minimum ({$this->product->stock} < {$this->product->min_stock}).",
            'warehouse_id' => $this->product->warehouse_id,
        ];
    }
}
