<?php

namespace App\Services;

use App\Jobs\TransferStockJob;
use App\Models\AuditLog;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\StockTransfer;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransferService
{
    /**
     * Membuat pengajuan transfer baru dengan status 'pending'.
     * Field dari Web form: source_warehouse_id / destination_warehouse_id
     * Field di tabel: from_warehouse_id / to_warehouse_id
     */
    public function requestTransfer(array $data, User $user): StockTransfer
    {
        $transfer = StockTransfer::create([
            'product_id'        => $data['product_id'],
            'from_warehouse_id' => $data['source_warehouse_id'],
            'to_warehouse_id'   => $data['destination_warehouse_id'],
            'quantity'          => $data['quantity'],
            'user_id'           => $user->id,
            'status'            => 'pending',
        ]);

        AuditLog::record('transfer.requested', $transfer, null, $transfer->toArray());

        return $transfer;
    }

    /**
     * Admin/Manajer menyetujui transfer → dispatch job ke queue.
     */
    public function approveTransfer(StockTransfer $transfer, User $approver): void
    {
        if ($transfer->status !== 'pending') {
            throw new Exception("Transfer #{$transfer->id} sudah diproses (status: {$transfer->status}).");
        }

        $transfer->update(['status' => 'approved']);
        AuditLog::record('transfer.approved', $transfer, ['status' => 'pending'], ['status' => 'approved']);

        // Eksekusi pemindahan stok via background job
        TransferStockJob::dispatch(['transfer_id' => $transfer->id])->onQueue('default');
    }

    /**
     * Admin/Manajer menolak transfer.
     */
    public function rejectTransfer(StockTransfer $transfer, User $rejector): void
    {
        if ($transfer->status !== 'pending') {
            throw new Exception("Transfer #{$transfer->id} sudah diproses (status: {$transfer->status}).");
        }

        $transfer->update(['status' => 'rejected']);
        AuditLog::record('transfer.rejected', $transfer, ['status' => 'pending'], ['status' => 'rejected']);
    }

    /**
     * Eksekusi pemindahan stok aktual — dipanggil oleh TransferStockJob.
     * Transfer harus berstatus 'approved'.
     */
    public function executeTransfer(array $transferData): void
    {
        DB::transaction(function () use ($transferData) {
            $transfer = StockTransfer::findOrFail($transferData['transfer_id']);

            if ($transfer->status !== 'approved') {
                throw new Exception("Transfer #{$transfer->id} belum disetujui (status: {$transfer->status}).");
            }

            $product = Product::findOrFail($transfer->product_id);

            $sourceProduct = Product::where('sku', $product->sku)
                ->where('warehouse_id', $transfer->from_warehouse_id)
                ->firstOrFail();

            if ($sourceProduct->stock < $transfer->quantity) {
                $transfer->update(['status' => 'rejected']);
                AuditLog::record('transfer.rejected', $transfer, null, ['reason' => 'Stok tidak mencukupi']);
                throw new Exception("Stok tidak mencukupi di gudang asal.");
            }

            $targetProduct = Product::firstOrCreate(
                ['sku' => $product->sku, 'warehouse_id' => $transfer->to_warehouse_id],
                [
                    'name'        => $product->name,
                    'min_stock'   => $product->min_stock,
                    'category_id' => $product->category_id,
                    'stock'       => 0,
                ]
            );

            // Catat transaksi keluar dari gudang asal
            StockTransaction::create([
                'product_id'   => $sourceProduct->id,
                'warehouse_id' => $transfer->from_warehouse_id,
                'user_id'      => $transfer->user_id,
                'type'         => 'out',
                'quantity'     => $transfer->quantity,
                'notes'        => "Transfer keluar ke gudang #{$transfer->to_warehouse_id} (Transfer #{$transfer->id})",
            ]);
            $sourceProduct->decrement('stock', $transfer->quantity);
            AlertService::checkAndAlert($sourceProduct->fresh());

            // Catat transaksi masuk ke gudang tujuan
            StockTransaction::create([
                'product_id'   => $targetProduct->id,
                'warehouse_id' => $transfer->to_warehouse_id,
                'user_id'      => $transfer->user_id,
                'type'         => 'in',
                'quantity'     => $transfer->quantity,
                'notes'        => "Transfer masuk dari gudang #{$transfer->from_warehouse_id} (Transfer #{$transfer->id})",
            ]);
            $targetProduct->increment('stock', $transfer->quantity);

            $transfer->update(['status' => 'completed']);
            AuditLog::record('transfer.completed', $transfer, null, $transfer->fresh()->toArray());

            Log::info("Transfer #{$transfer->id} selesai dieksekusi.", [
                'from' => $transfer->from_warehouse_id,
                'to'   => $transfer->to_warehouse_id,
                'qty'  => $transfer->quantity,
            ]);
        });
    }
}
