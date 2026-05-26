<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\StockTransfer;
use App\Models\Warehouse;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\TransferService;

class TransferController extends Controller
{
    public function __construct(private TransferService $transferService) {}

    public function index()
    {
        $transfers = StockTransfer::with(['product', 'sourceWarehouse', 'destinationWarehouse', 'requestedBy'])->latest()->paginate(10);
        return view('transfers.index', compact('transfers'));
    }

    public function create()
    {
        $missingData = [];
        if (Product::count() === 0) $missingData[] = 'Produk';
        if (Warehouse::count() < 2) $missingData[] = 'Minimal 2 Gudang (untuk pengirim dan penerima)';

        if (count($missingData) > 0) {
            return redirect()->route('transfers.index')->with('error', 'Anda tidak dapat membuat transfer karena data berikut belum ada: ' . implode(', ', $missingData));
        }

        $warehouses = Warehouse::all();
        $products = Product::all();
        return view('transfers.create', compact('warehouses', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'source_warehouse_id' => 'required|exists:warehouses,id',
            'destination_warehouse_id' => 'required|exists:warehouses,id|different:source_warehouse_id',
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string',
        ]);

        try {
            $this->transferService->requestTransfer($validated, auth()->user());
            return redirect()->route('transfers.index')->with('success', 'Pengajuan transfer berhasil dibuat.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, StockTransfer $transfer)
    {
        // For approving/rejecting
        $request->validate(['status' => 'required|in:approved,rejected']);
        
        try {
            if ($request->status === 'approved') {
                $this->transferService->approveTransfer($transfer, auth()->user());
            } else {
                $this->transferService->rejectTransfer($transfer, auth()->user());
            }
            return redirect()->route('transfers.index')->with('success', 'Status transfer berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
