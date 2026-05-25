<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\StockTransaction;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistik agregat untuk Chart.js (trend transaksi 30 hari terakhir)
        $trends = StockTransaction::selectRaw('DATE(created_at) as date, type, SUM(quantity) as total')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date', 'type')
            ->get();

        // Data Gudang untuk Peta (Leaflet.js)
        $warehouses = Warehouse::select('id', 'name', 'latitude', 'longitude')->get();

        // Produk dengan stok kritis
        $lowStockProducts = Product::whereColumn('stock', '<', 'min_stock')->with('warehouse')->get();

        return response()->json([
            'trends' => $trends,
            'warehouses' => $warehouses,
            'low_stock_alerts' => $lowStockProducts,
            'total_products' => Product::count(),
            'total_warehouses' => Warehouse::count(),
        ]);
    }
}
