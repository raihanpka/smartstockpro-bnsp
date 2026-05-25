<?php

namespace App\Services;

use App\Models\StockTransfer;
use App\Models\StockTransaction;
use App\Models\Product;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Exception;

class TransferService
{
    public function executeTransfer(array $transferData): void
    {
        DB::transaction(function () use ($transferData) {
            $transfer = StockTransfer::findOrFail($transferData['transfer_id']);

            if ($transfer->status !== 'pending') {
                throw new Exception("Transfer is already processed.");
            }

            $product = Product::findOrFail($transfer->product_id);
            
            // Check stock at source warehouse
            // Asumsi product stock column holds total stock? 
            // Wait, if it's per warehouse, we need a different approach.
            // Based on AGENTS.md, product has `warehouse_id` and `stock`.
            // So transferring means decreasing stock on source product, and increasing on target product (or creating if it doesn't exist).
            
            $sourceProduct = Product::where('sku', $product->sku)
                ->where('warehouse_id', $transfer->from_warehouse_id)
                ->firstOrFail();

            if ($sourceProduct->stock < $transfer->quantity) {
                $transfer->update(['status' => 'rejected']);
                AuditLog::record('transfer.rejected', $transfer, null, ['reason' => 'Insufficient stock']);
                throw new Exception("Insufficient stock at source warehouse.");
            }

            $targetProduct = Product::firstOrCreate(
                ['sku' => $product->sku, 'warehouse_id' => $transfer->to_warehouse_id],
                [
                    'name' => $product->name,
                    'min_stock' => $product->min_stock,
                    'category_id' => $product->category_id,
                    'stock' => 0
                ]
            );

            // Record Outbound
            StockTransaction::create([
                'product_id' => $sourceProduct->id,
                'warehouse_id' => $transfer->from_warehouse_id,
                'user_id' => $transfer->user_id,
                'type' => 'out',
                'quantity' => $transfer->quantity,
                'notes' => 'Transfer out to warehouse ' . $transfer->to_warehouse_id
            ]);
            $sourceProduct->decrement('stock', $transfer->quantity);
            AlertService::checkAndAlert($sourceProduct);

            // Record Inbound
            StockTransaction::create([
                'product_id' => $targetProduct->id,
                'warehouse_id' => $transfer->to_warehouse_id,
                'user_id' => $transfer->user_id,
                'type' => 'in',
                'quantity' => $transfer->quantity,
                'notes' => 'Transfer in from warehouse ' . $transfer->from_warehouse_id
            ]);
            $targetProduct->increment('stock', $transfer->quantity);

            $transfer->update(['status' => 'completed']);
            AuditLog::record('transfer.completed', $transfer, null, $transfer->toArray());
        });
    }
}
