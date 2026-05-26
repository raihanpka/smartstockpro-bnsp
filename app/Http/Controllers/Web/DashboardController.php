<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Category;
use App\Models\StockTransaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalProducts = Product::count();
        $totalWarehouses = Warehouse::count();
        $totalCategories = Category::count();
        
        $lowStockProducts = Product::with(['category', 'warehouse'])
            ->whereColumn('stock', '<=', 'min_stock')
            ->get();
            
        $lowStockCount = $lowStockProducts->count();
        
        // Prepare chart data (Last 6 months Inbound vs Outbound)
        $months = [];
        $inboundData = [];
        $outboundData = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $months[] = $month->format('M');
            
            $inbound = StockTransaction::where('type', 'in')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('quantity');
                
            $outbound = StockTransaction::where('type', 'out')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('quantity');
                
            $inboundData[] = $inbound;
            $outboundData[] = $outbound;
        }

        return view('dashboard', compact(
            'totalProducts',
            'totalWarehouses',
            'totalCategories',
            'lowStockProducts',
            'lowStockCount',
            'months',
            'inboundData',
            'outboundData'
        ));
    }
}
