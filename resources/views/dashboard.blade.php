<x-app-layout>
    <x-slot name="header">
        Dashboard
    </x-slot>

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <x-card class="p-6">
            <div class="flex items-center justify-between space-y-0 pb-2">
                <h3 class="tracking-tight text-sm font-medium text-slate-500">Total Products</h3>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" class="h-4 w-4 text-slate-400"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
            </div>
            <div class="text-2xl font-bold text-slate-950">{{ number_format($totalProducts) }}</div>
            <p class="text-xs text-slate-500 mt-1">Across all warehouses</p>
        </x-card>
        <x-card class="p-6">
            <div class="flex items-center justify-between space-y-0 pb-2">
                <h3 class="tracking-tight text-sm font-medium text-slate-500">Low Stock Alerts</h3>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" class="h-4 w-4 text-red-500"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0zM12 9v4M12 17h.01"></path></svg>
            </div>
            <div class="text-2xl font-bold text-red-600">{{ number_format($lowStockCount) }}</div>
            <p class="text-xs text-slate-500 mt-1">Requires immediate attention</p>
        </x-card>
        <x-card class="p-6">
            <div class="flex items-center justify-between space-y-0 pb-2">
                <h3 class="tracking-tight text-sm font-medium text-slate-500">Active Warehouses</h3>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" class="h-4 w-4 text-slate-400"><rect width="20" height="14" x="2" y="5" rx="2"></rect><path d="M2 10h20"></path></svg>
            </div>
            <div class="text-2xl font-bold text-slate-950">{{ number_format($totalWarehouses) }}</div>
        </x-card>
        <x-card class="p-6">
            <div class="flex items-center justify-between space-y-0 pb-2">
                <h3 class="tracking-tight text-sm font-medium text-slate-500">Total Categories</h3>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" class="h-4 w-4 text-slate-400"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
            </div>
            <div class="text-2xl font-bold text-slate-950">{{ number_format($totalCategories) }}</div>
        </x-card>
    </div>

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-7 mt-4">
        <x-card class="col-span-4 p-6 flex flex-col">
            <div class="mb-4">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Overview</h3>
                <p class="text-sm text-slate-500 mt-1">Stok masuk vs keluar bulan ini.</p>
            </div>
            <div class="flex-1 w-full h-64">
                <canvas id="overviewChart"></canvas>
            </div>
        </x-card>
        <x-card class="col-span-3 p-6 flex flex-col">
            <div class="mb-4">
                <h3 class="text-lg font-semibold leading-none tracking-tight">System Monitoring</h3>
                <p class="text-sm text-slate-500 mt-1">Resource server (CPU & Memory).</p>
            </div>
            <div class="flex-1 mt-2 space-y-4">
                <div>
                    <div class="flex justify-between mb-1">
                        <span class="text-sm font-medium text-slate-700">CPU Usage</span>
                        <span class="text-sm font-medium text-slate-700">{{ sys_getloadavg()[0] }}%</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-2">
                        <div class="bg-slate-900 h-2 rounded-full" style="width: {{ min(100, sys_getloadavg()[0] * 10) }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between mb-1">
                        <span class="text-sm font-medium text-slate-700">Memory Usage</span>
                        <span class="text-sm font-medium text-slate-700">{{ round(memory_get_usage() / 1048576, 2) }} MB</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ min(100, (memory_get_usage() / 134217728) * 100) }}%"></div>
                    </div>
                </div>
                <div class="pt-4 border-t border-slate-100">
                    <a href="/system-logs" class="text-sm font-medium text-indigo-600 hover:underline">View System Logs &rarr;</a>
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
            <div class="overflow-x-auto">
                <x-table>
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50/50">
                            <th class="h-12 px-4 text-left align-middle font-medium text-slate-500">SKU</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-slate-500">Nama Produk</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-slate-500">Gudang</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-slate-500">Kategori</th>
                            <th class="h-12 px-4 text-right align-middle font-medium text-slate-500">Stok Saat Ini</th>
                            <th class="h-12 px-4 text-right align-middle font-medium text-slate-500">Batas Minimum</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lowStockProducts as $product)
                            <tr class="border-b border-slate-200 hover:bg-slate-50">
                                <td class="p-4 align-middle text-sm">{{ $product->sku }}</td>
                                <td class="p-4 align-middle text-sm font-medium">{{ $product->name }}</td>
                                <td class="p-4 align-middle text-sm">{{ $product->warehouse->name ?? '-' }}</td>
                                <td class="p-4 align-middle text-sm">
                                    <x-badge class="bg-slate-100 text-slate-800">{{ $product->category->name ?? '-' }}</x-badge>
                                </td>
                                <td class="p-4 align-middle text-sm text-right font-bold text-red-600">{{ number_format($product->stock) }}</td>
                                <td class="p-4 align-middle text-sm text-right text-slate-500">{{ number_format($product->minimum_stock) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-table>
            </div>
        </x-card>
    </div>
    @endif

    <!-- Chart.js Script -->
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
    </script>
</x-app-layout>
