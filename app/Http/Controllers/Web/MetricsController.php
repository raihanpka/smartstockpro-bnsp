<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class MetricsController extends Controller
{
    /**
     * Endpoint untuk polling real-time resource server.
     * Dipanggil via AJAX setiap beberapa detik dari dashboard.
     */
    public function index()
    {
        $load      = sys_getloadavg();
        $cpuLoad   = round($load[0] * 10, 1); // normalisasi ke % (1-menit load avg × 10)
        $memTotal  = $this->getMemoryTotal();
        $memUsed   = memory_get_usage(true);
        $memPct    = $memTotal > 0 ? round(($memUsed / $memTotal) * 100, 1) : 0;

        return response()->json([
            'cpu_pct'       => min(100, $cpuLoad),
            'cpu_raw'       => $load[0],
            'mem_used_mb'   => round($memUsed / 1048576, 1),
            'mem_total_mb'  => round($memTotal / 1048576, 1),
            'mem_pct'       => min(100, $memPct),
            'timestamp'     => now()->format('H:i:s'),
        ]);
    }

    /** Coba baca total RAM dari /proc/meminfo (Linux) atau fallback 128 MB */
    private function getMemoryTotal(): int
    {
        if (PHP_OS_FAMILY === 'Linux' && file_exists('/proc/meminfo')) {
            $content = file_get_contents('/proc/meminfo');
            preg_match('/MemTotal:\s+(\d+)\s+kB/', $content, $m);
            return isset($m[1]) ? (int) $m[1] * 1024 : 134217728;
        }
        return 134217728; // 128 MB fallback
    }
}
