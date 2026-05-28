<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\Warehouse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ReportService
{
    public function generateInventoryReport(array $params): string
    {
        // Ambil produk beserta relasi
        $productQuery = Product::with(['category', 'warehouse']);

        if (!empty($params['warehouse_id'])) {
            $productQuery->where('warehouse_id', $params['warehouse_id']);
        }

        $products = $productQuery->orderBy('name')->get();

        // Transaksi 30 hari terakhir (atau sesuai filter tanggal)
        $txQuery = StockTransaction::with(['product', 'warehouse'])
            ->orderBy('created_at', 'desc')
            ->limit(100);

        if (!empty($params['start_date'])) {
            $txQuery->whereDate('created_at', '>=', $params['start_date']);
        } else {
            $txQuery->where('created_at', '>=', now()->subDays(30));
        }

        if (!empty($params['end_date'])) {
            $txQuery->whereDate('created_at', '<=', $params['end_date']);
        }

        $transactions = $txQuery->get();

        $data = [
            'title'        => 'Laporan Inventaris',
            'date'         => now()->format('d M Y, H:i'),
            'filters'      => $params,
            'products'     => $products,
            'transactions' => $transactions,
            'summary'      => [
                'total_products'   => $products->count(),
                'total_stock'      => $products->sum('stock'),
                'total_warehouses' => Warehouse::count(),
                'low_stock_count'  => $products->filter(fn($p) => $p->stock < $p->min_stock)->count(),
            ],
        ];

        $pdf      = Pdf::loadView('report.inventory_pdf', $data)->setPaper('a4', 'landscape');
        $fileName = 'reports/inventory_' . now()->format('Ymd_His') . '.pdf';

        Storage::put($fileName, $pdf->output());

        AuditLog::record('report.generated', null, null, [
            'file'   => $fileName,
            'params' => $params,
        ]);

        return $fileName;
    }
}
