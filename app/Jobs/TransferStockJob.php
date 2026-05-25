<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\TransferService;
use Exception;
use Illuminate\Support\Facades\Log;

class TransferStockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(private array $transferData)
    {
    }

    public function handle(TransferService $transferService): void
    {
        try {
            $transferService->executeTransfer($this->transferData);
        } catch (Exception $e) {
            Log::error("TransferStockJob failed: " . $e->getMessage(), ['transferData' => $this->transferData]);
            throw $e;
        }
    }
}
