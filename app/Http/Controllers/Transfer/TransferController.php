<?php

namespace App\Http\Controllers\Transfer;

use App\Http\Controllers\Controller;
use App\Models\StockTransfer;
use Illuminate\Http\Request;
use App\Jobs\TransferStockJob;

class TransferController extends Controller
{
    public function index()
    {
        $transfers = StockTransfer::with(['product', 'from_warehouse', 'to_warehouse'])
            ->latest()
            ->paginate(25);
            
        return response()->json($transfers);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'from_warehouse_id' => 'required|exists:warehouses,id|different:to_warehouse_id',
            'to_warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|integer|min:1',
        ]);
        
        $validated['user_id'] = auth()->id() ?? 1; // Fallback for testing without auth
        $validated['status'] = 'pending';

        $transfer = StockTransfer::create($validated);
        
        // Dispatch job for background processing
        TransferStockJob::dispatch(['transfer_id' => $transfer->id])->onQueue('default');

        return response()->json([
            'message' => 'Transfer requested and is being processed in background',
            'data' => $transfer
        ], 201);
    }
}
