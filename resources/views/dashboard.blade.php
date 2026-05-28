<x-app-layout>
    <x-slot name="header">
        Dashboard
    </x-slot>

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <x-card class="p-6">
            <div class="flex items-center justify-between space-y-0 pb-2">
                <h3 class="tracking-tight text-sm font-medium text-slate-500">Total Produk</h3>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" class="h-4 w-4 text-slate-400"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
            </div>
            <div class="text-2xl font-bold text-slate-950">{{ number_format($totalProducts) }}</div>
            <p class="text-xs text-slate-500 mt-1">Di seluruh gudang</p>
        </x-card>
        <x-card class="p-6">
            <div class="flex items-center justify-between space-y-0 pb-2">
                <h3 class="tracking-tight text-sm font-medium text-slate-500">Peringatan Stok</h3>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" class="h-4 w-4 text-red-500"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0zM12 9v4M12 17h.01"></path></svg>
            </div>
            <div class="text-2xl font-bold text-red-600">{{ number_format($lowStockCount) }}</div>
            <p class="text-xs text-slate-500 mt-1">Butuh perhatian segera</p>
        </x-card>
        <x-card class="p-6">
            <div class="flex items-center justify-between space-y-0 pb-2">
                <h3 class="tracking-tight text-sm font-medium text-slate-500">Gudang Aktif</h3>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" class="h-4 w-4 text-slate-400"><rect width="16" height="20" x="4" y="2" rx="2" ry="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M12 6h.01"/><path d="M12 10h.01"/><path d="M12 14h.01"/><path d="M16 10h.01"/><path d="M16 14h.01"/><path d="M8 10h.01"/><path d="M8 14h.01"/></svg>
            </div>
            <div class="text-2xl font-bold text-slate-950">{{ number_format($totalWarehouses) }}</div>
            <p class="text-xs text-slate-500 mt-1">Terdaftar aktif</p>
        </x-card>
        <x-card class="p-6">
            <div class="flex items-center justify-between space-y-0 pb-2">
                <h3 class="tracking-tight text-sm font-medium text-slate-500">Total Kategori</h3>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" class="h-4 w-4 text-slate-400"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
            </div>
            <div class="text-2xl font-bold text-slate-950">{{ number_format($totalCategories) }}</div>
            <p class="text-xs text-slate-500 mt-1">Klasifikasi produk</p>
        </x-card>
    </div>

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-7 mt-4">
        <x-card class="col-span-4 p-6 flex flex-col">
            <div class="mb-4">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Ringkasan</h3>
                <p class="text-sm text-slate-500 mt-1">Stok masuk vs keluar bulan ini.</p>
            </div>
            <div class="flex-1 w-full h-64">
                <canvas id="overviewChart"></canvas>
            </div>
        </x-card>
        <x-card class="col-span-3 p-6 flex flex-col">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold leading-none tracking-tight">Pemantauan Sistem</h3>
                    <p class="text-sm text-slate-500 mt-1">Sumber daya server (CPU & Memori).</p>
                </div>
                <span id="metrics-ts" class="text-xs text-slate-400">--:--:--</span>
            </div>
            <div class="flex-1 mt-2 space-y-4">
                <div>
                    <div class="flex justify-between mb-1">
                        <span class="text-sm font-medium text-slate-700">Penggunaan CPU</span>
                        <span id="cpu-pct" class="text-sm font-medium text-slate-700">--%</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-2">
                        <div id="cpu-bar" class="bg-slate-900 h-2 rounded-full transition-all duration-500" style="width: 0%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between mb-1">
                        <span class="text-sm font-medium text-slate-700">Penggunaan Memori</span>
                        <span id="mem-pct" class="text-sm font-medium text-slate-700">-- MB</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-2">
                        <div id="mem-bar" class="bg-blue-600 h-2 rounded-full transition-all duration-500" style="width: 0%"></div>
                    </div>
                </div>
                <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                    <a href="/system-logs" class="text-sm font-medium text-indigo-600 hover:underline">Lihat Log Sistem &rarr;</a>
                    <span class="text-xs text-slate-400">Auto-refresh 5 dtk</span>
                </div>
            </div>
        </x-card>
    </div>

    @if($lowStockCount > 0)
    <div class="mt-4">
        <x-card class="p-6">
            <div class="mb-4">
                <h3 class="text-lg font-semibold leading-none tracking-tight text-red-600">Peringatan Stok Kritis</h3>
                <p class="text-sm text-slate-500 mt-1">Produk berikut telah mencapai atau berada di bawah batas minimum stok.</p>
            </div>
            <x-table>
                <x-slot name="header">
                    <th class="h-10 px-4 text-left align-middle font-medium">SKU</th>
                    <th class="h-10 px-4 text-left align-middle font-medium">Nama Produk</th>
                    <th class="h-10 px-4 text-left align-middle font-medium">Gudang</th>
                    <th class="h-10 px-4 text-left align-middle font-medium">Kategori</th>
                    <th class="h-10 px-4 text-right align-middle font-medium text-red-600">Stok Saat Ini</th>
                    <th class="h-10 px-4 text-right align-middle font-medium text-slate-500">Batas Minimum</th>
                </x-slot>

                @foreach($lowStockProducts as $product)
                <tr class="border-b transition-colors hover:bg-slate-50/50">
                    <td class="p-4 align-middle font-medium text-sm">{{ $product->sku }}</td>
                    <td class="p-4 align-middle text-sm font-semibold">{{ $product->name }}</td>
                    <td class="p-4 align-middle text-sm text-slate-500">{{ $product->warehouse->name ?? '-' }}</td>
                    <td class="p-4 align-middle text-sm">
                        <x-badge class="bg-slate-100 text-slate-800">{{ $product->category->name ?? '-' }}</x-badge>
                    </td>
                    <td class="p-4 align-middle text-sm text-right font-bold text-red-600">{{ number_format($product->stock) }}</td>
                    <td class="p-4 align-middle text-sm text-right text-slate-500">{{ number_format($product->min_stock) }}</td>
                </tr>
                @endforeach
            </x-table>
        </x-card>
    </div>
    @endif

    <!-- Chart.js + Metrics Polling -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('overviewChart');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($months) !!},
                datasets: [{
                    label: 'Inbound',
                    data: {!! json_encode($inboundData) !!},
                    backgroundColor: '#10b981',
                }, {
                    label: 'Outbound',
                    data: {!! json_encode($outboundData) !!},
                    backgroundColor: '#f97316',
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
            });

            // ── Auto-refresh metrics panel (polling setiap 5 detik) ──
            async function fetchMetrics() {
                try {
                    const res  = await fetch('{{ route('metrics') }}', {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const data = await res.json();

                    document.getElementById('cpu-pct').textContent  = data.cpu_pct + '%';
                    document.getElementById('cpu-bar').style.width  = data.cpu_pct + '%';

                    document.getElementById('mem-pct').textContent  = data.mem_used_mb + ' MB / ' + data.mem_total_mb + ' MB (' + data.mem_pct + '%)';
                    document.getElementById('mem-bar').style.width  = data.mem_pct + '%';

                    // Warna bar CPU berdasarkan beban
                    const cpuBar = document.getElementById('cpu-bar');
                    cpuBar.className = 'h-2 rounded-full transition-all duration-500 ' +
                        (data.cpu_pct >= 80 ? 'bg-red-500' : data.cpu_pct >= 50 ? 'bg-orange-400' : 'bg-slate-900');

                    document.getElementById('metrics-ts').textContent = data.timestamp;
                } catch (e) {
                    document.getElementById('metrics-ts').textContent = 'Error';
                }
            }

        fetchMetrics();
        setInterval(fetchMetrics, 5000);
    </script>
</x-app-layout>
