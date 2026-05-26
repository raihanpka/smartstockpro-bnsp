<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\StockTransaction;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use App\Services\StockCalculationService;

class TransactionController extends Controller
{
    public function __construct(private StockCalculationService $stockService) {}

    public function index()
    {
        $transactions = StockTransaction::with(['product', 'warehouse'])->latest()->paginate(10);
        return view('transactions.index', compact('transactions'));
    }

    public function create()
    {
        $missingData = [];
        if (Product::count() === 0) $missingData[] = 'Produk';
        if (Warehouse::count() === 0) $missingData[] = 'Gudang';

        if (count($missingData) > 0) {
            return redirect()->route('transactions.index')->with('error', 'Anda tidak dapat membuat transaksi karena data berikut belum ada: ' . implode(', ', $missingData));
        }

        $products = Product::all();
        $warehouses = Warehouse::all();
        return view('transactions.create', compact('products', 'warehouses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['user_id'] = auth()->id();

        try {
            if ($request->type === 'in') {
                $this->stockService->recordInbound($validated);
            } else {
                $this->stockService->recordOutbound($validated);
            }
            return redirect()->route('transactions.index')->with('success', 'Transaksi stok berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
