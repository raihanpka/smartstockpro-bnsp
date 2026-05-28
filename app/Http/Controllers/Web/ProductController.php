<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use App\Services\StockCalculationService;
use App\Services\ReportService;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function __construct(private StockCalculationService $stockService) {}

    public function index(Request $request)
    {
        $query = Product::with(['category', 'warehouse']);

        // Pencarian nama / SKU
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Filter gudang
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // Filter kategori
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter stok kritis saja
        if ($request->filled('low_stock') && $request->low_stock === '1') {
            $query->whereColumn('stock', '<=', 'min_stock');
        }

        // Sorting
        $sortable = ['name', 'sku', 'stock', 'created_at'];
        $sort     = in_array($request->sort, $sortable) ? $request->sort : 'created_at';
        $dir      = $request->dir === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sort, $dir);

        $products   = $query->paginate(10)->withQueryString();
        $warehouses = Warehouse::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return view('products.index', compact('products', 'warehouses', 'categories'));
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

    public function export(ReportService $reportService)
    {
        $fileName = $reportService->generateInventoryReport(['type' => 'inventory']);
        return Storage::download($fileName);
    }

    public function import(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'file'         => 'required|mimes:xlsx,xls,csv|max:5120',
        ]);

        // Simpan file ke storage/app/imports/ agar bisa diakses oleh job
        $path = $request->file('file')->store('imports');

        // Dispatch job ke queue (in-memory via sync driver)
        \App\Jobs\ImportProductsJob::dispatch($path, (int) $request->warehouse_id, auth()->id())
            ->onQueue('imports');

        return back()->with('success', 'File berhasil diunggah dan sedang diproses. Refresh halaman untuk melihat produk terbaru.');
    }
}
