<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Warehouse;
use App\Models\Supplier;

class CatalogController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->get();
        $warehouses = Warehouse::all();
        $suppliers = Supplier::all();

        return view('master.catalog.index', compact('categories', 'warehouses', 'suppliers'));
    }
}
