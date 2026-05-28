<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportService
{
    /**
     * Proses import produk dari file CSV/Excel yang sudah disimpan di storage.
     * Dipanggil oleh ImportProductsJob (background).
     */
    public function processImport(string $filePath, int $warehouseId, int $userId): array
    {
        $fullPath = Storage::path($filePath);

        if (!file_exists($fullPath)) {
            throw new \Exception("File import tidak ditemukan: {$filePath}");
        }

        $rows    = $this->parseFile($fullPath);
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors  = [];

        foreach ($rows as $index => $row) {
            $lineNo = $index + 2; // baris 1 = header

            // Kolom wajib
            if (empty($row['name']) || empty($row['sku'])) {
                $errors[] = "Baris {$lineNo}: kolom 'name' dan 'sku' wajib diisi.";
                $skipped++;
                continue;
            }

            // Cari atau buat kategori
            $categoryId = null;
            if (!empty($row['category'])) {
                $category   = Category::firstOrCreate(['name' => trim($row['category'])]);
                $categoryId = $category->id;
            }

            $payload = array_filter([
                'name'        => trim($row['name']),
                'sku'         => trim($row['sku']),
                'stock'       => (int) ($row['stock']    ?? 0),
                'min_stock'   => (int) ($row['min_stock'] ?? 10),
                'warehouse_id' => $warehouseId,
                'category_id' => $categoryId,
            ], fn($v) => $v !== null);

            // Upsert berdasarkan SKU + warehouse
            $existing = Product::where('sku', $payload['sku'])
                ->where('warehouse_id', $warehouseId)
                ->first();

            if ($existing) {
                $existing->update($payload);
                $updated++;
            } else {
                Product::create($payload);
                $created++;
            }
        }

        $summary = [
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'errors'  => $errors,
        ];

        Log::info("Import selesai.", array_merge($summary, [
            'file'         => $filePath,
            'warehouse_id' => $warehouseId,
        ]));

        AuditLog::record('import.processed', null, null, array_merge($summary, [
            'file'         => $filePath,
            'warehouse_id' => $warehouseId,
            'user_id'      => $userId,
        ]));

        // Hapus file temp setelah diproses
        Storage::delete($filePath);

        return $summary;
    }

    /**
     * Parse CSV atau Excel (xlsx/xls) menjadi array asosiatif.
     */
    private function parseFile(string $fullPath): array
    {
        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        if ($ext === 'csv') {
            return $this->parseCsv($fullPath);
        }

        // Untuk xlsx/xls gunakan maatwebsite/excel via temp konversi ke CSV
        // Jika package tersedia, delegasikan; fallback ke CSV parser
        if (class_exists(\Maatwebsite\Excel\Facades\Excel::class)) {
            $rows   = \Maatwebsite\Excel\Facades\Excel::toArray(new \stdClass(), $fullPath)[0] ?? [];
            $header = array_map(fn($h) => Str::snake(strtolower(trim((string) $h))), array_shift($rows) ?? []);
            return array_map(fn($row) => array_combine($header, array_pad($row, count($header), null)), $rows);
        }

        throw new \Exception("Format file '{$ext}' tidak didukung. Gunakan CSV.");
    }

    private function parseCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        $header = array_map(fn($h) => Str::snake(strtolower(trim($h))), fgetcsv($handle));
        $rows   = [];

        while (($line = fgetcsv($handle)) !== false) {
            if (count($line) === count($header)) {
                $rows[] = array_combine($header, $line);
            }
        }

        fclose($handle);
        return $rows;
    }
}
