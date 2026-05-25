<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Log;

class ImportService
{
    public function processImport(string $filePath, int $warehouseId, int $userId): void
    {
        try {
            // Simulasi parsing file excel menggunakan maatwebsite/excel
            // \Maatwebsite\Excel\Facades\Excel::import(new ProductsImport($warehouseId), $filePath);
            
            Log::info("Processing import from file: {$filePath} for warehouse: {$warehouseId}");
            
            AuditLog::record('import.processed', null, null, [
                'file' => $filePath,
                'warehouse_id' => $warehouseId,
                'user_id' => $userId
            ]);
            
        } catch (\Exception $e) {
            Log::error("Failed to process import: " . $e->getMessage());
            throw $e;
        }
    }
}
