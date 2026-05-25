<?php

namespace App\Services;

use App\Models\AuditLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Exception;

class ReportService
{
    public function generateInventoryReport(array $params): string
    {
        // Simulasi query data untuk laporan
        // $data = ...
        
        $data = [
            'title' => 'Laporan Inventaris',
            'date' => now()->format('Y-m-d H:i:s'),
            'filters' => $params
        ];

        try {
            $pdf = Pdf::loadView('report.inventory_pdf', $data);
            $fileName = 'reports/inventory_' . time() . '.pdf';
            
            Storage::put($fileName, $pdf->output());
            
            AuditLog::record('report.generated', null, null, ['file' => $fileName, 'params' => $params]);
            
            return $fileName;
        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to generate report: " . $e->getMessage());
            throw $e;
        }
    }
}
