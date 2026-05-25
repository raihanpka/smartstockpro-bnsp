<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class SystemLogController extends Controller
{
    public function index()
    {
        $logPath = storage_path('logs/laravel.log');
        $logs = [];

        if (File::exists($logPath)) {
            // Read last 100 lines for simplicity
            $file = file($logPath);
            $lines = array_slice($file, -100);
            
            foreach ($lines as $line) {
                if (preg_match('/\[(.*?)\] (.*?)\.(ERROR|WARNING|INFO): (.*)/', $line, $matches)) {
                    $logs[] = [
                        'timestamp' => $matches[1],
                        'env' => $matches[2],
                        'severity' => $matches[3],
                        'message' => $matches[4],
                    ];
                }
            }
        }

        $logs = array_reverse($logs);

        return view('system-logs', compact('logs'));
    }
}
