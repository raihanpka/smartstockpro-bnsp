<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\ReportService;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

class GenerateReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        private array $params,
        private int $userId
    ) {}

    public function handle(ReportService $reportService): void
    {
        $filePath = $reportService->generateInventoryReport($this->params);
        
        // Asumsi kita notify user jika laporan sudah selesai di generate.
        $user = User::find($this->userId);
        if ($user) {
            // Notification::send($user, new ReportGeneratedNotification($filePath));
            \Illuminate\Support\Facades\Log::info("Report generated and saved at {$filePath}. User notified.");
        }
    }
}
