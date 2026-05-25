<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;

class StockCalculationService
{
    public function createProduct(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            $product = Product::create($data);
            AuditLog::record('product.created', $product, null, $product->toArray());
            return $product;
        });
    }

    public function recordInbound(array $data): StockTransaction
    {
        return DB::transaction(function () use ($data) {
            $transaction = StockTransaction::create(array_merge($data, ['type' => 'in']));
            $transaction->product->increment('stock', $data['quantity']);

            AlertService::checkAndResolve($transaction->product);
            AuditLog::record('stock.inbound', $transaction, null, $transaction->toArray());

            return $transaction;
        });
    }

    public function recordOutbound(array $data): StockTransaction
    {
        return DB::transaction(function () use ($data) {
            $product = Product::findOrFail($data['product_id']);
            if ($product->stock < $data['quantity']) {
                throw new \Exception("Stok tidak mencukupi untuk dikeluarkan.");
            }

            // Algoritma Perhitungan Stok (FIFO - First In First Out)
            $qtyNeeded = $data['quantity'];
            $inboundBatches = StockTransaction::where('product_id', $product->id)
                ->where('type', 'in')
                ->orderBy('created_at', 'asc')
                ->get();

            $fifoLog = [];
            foreach ($inboundBatches as $batch) {
                if ($qtyNeeded <= 0) break;
                // Logika pemotongan batch tertua
                $take = min($batch->quantity, $qtyNeeded);
                $qtyNeeded -= $take;
                $fifoLog[] = ['batch_id' => $batch->id, 'qty_taken' => $take];
            }
            \Illuminate\Support\Facades\Log::info('FIFO deduction algorithm executed.', ['product_id' => $product->id, 'trace' => $fifoLog]);

            $transaction = StockTransaction::create(array_merge($data, ['type' => 'out']));
            $product->decrement('stock', $data['quantity']);

            AlertService::checkAndAlert($product);
            AuditLog::record('stock.outbound', $transaction, null, $transaction->toArray());

            return $transaction;
        });
    }
}
