<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\StockTransaction;
use App\Http\Requests\StoreTransactionRequest;
use App\Services\StockCalculationService;

class TransactionController extends Controller
{
    public function __construct(private StockCalculationService $stockService) {}

    public function index()
    {
        return response()->json(StockTransaction::with(['product', 'warehouse'])->latest()->paginate(25));
    }

    public function store(StoreTransactionRequest $request)
    {
        $validated = $request->validated();
        $validated['user_id'] = auth()->id() ?? 1;

        if ($request->type === 'in') {
            $transaction = $this->stockService->recordInbound($validated);
        } else {
            $transaction = $this->stockService->recordOutbound($validated);
        }
        
        return response()->json($transaction, 201);
    }
}
