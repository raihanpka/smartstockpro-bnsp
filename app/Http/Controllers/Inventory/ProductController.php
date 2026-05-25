<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\StockCalculationService;

class ProductController extends Controller
{
    public function __construct(private StockCalculationService $stockService) {}

    public function index(Request $request)
    {
        $products = Product::with(['category', 'warehouse'])
            ->when($request->warehouse_id, fn($q) => $q->where('warehouse_id', $request->warehouse_id))
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->paginate(25);
            
        return response()->json($products);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku',
            'min_stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'warehouse_id' => 'required|exists:warehouses,id',
        ]);

        $product = $this->stockService->createProduct($validated);
        return response()->json(['message' => 'Product created successfully', 'data' => $product], 201);
    }
}
