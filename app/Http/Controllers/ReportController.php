<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\GenerateReportJob;

class ReportController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:inventory,transfer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        GenerateReportJob::dispatch($validated, $request->user()->id ?? 1)->onQueue('reports');

        return response()->json([
            'message' => 'Laporan sedang diproses di background. Anda akan menerima notifikasi jika sudah selesai.'
        ]);
    }
}
