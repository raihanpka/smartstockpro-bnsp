<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use App\Services\StockCalculationService;

class ProductController extends Controller
{
    public function __construct(private StockCalculationService $stockService) {}

    public function index()
    {
        $products = Product::with(['category', 'warehouse'])->latest()->paginate(10);
        return view('products.index', compact('products'));
    }

    public function create()
    {
        $missingMasterData = [];
        if (Category::count() === 0) $missingMasterData[] = 'Kategori';
        if (Warehouse::count() === 0) $missingMasterData[] = 'Gudang';
        if (\App\Models\Supplier::count() === 0) $missingMasterData[] = 'Pemasok';

        if (count($missingMasterData) > 0) {
            return redirect()->route('products.index')->with('error', 'Lengkapi master data berikut terlebih dahulu: ' . implode(', ', $missingMasterData));
        }

        $categories = Category::all();
        $warehouses = Warehouse::all();
        return view('products.create', compact('categories', 'warehouses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku',
            'min_stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('products', 'public');
        }

        $this->stockService->createProduct($validated);
        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        $warehouses = Warehouse::all();
        return view('products.edit', compact('product', 'categories', 'warehouses'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku,'.$product->id,
            'min_stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($product->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('products', 'public');
        }

        $product->update($validated);
        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
    }

    public function export()
    {
        \App\Jobs\GenerateReportJob::dispatch(['type' => 'inventory'], auth()->id())->onQueue('reports');
        return back()->with('success', 'Laporan inventaris sedang diproses di background.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);
        
        \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\ProductsImport($request->warehouse_id), $request->file('file'));
        return back()->with('success', 'Data produk berhasil diimpor.');
    }
}
