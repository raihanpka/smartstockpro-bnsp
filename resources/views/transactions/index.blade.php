<x-app-layout>
    <x-slot name="header">
        Transaksi Stok
    </x-slot>

    <div class="mb-4 flex justify-between items-center">
        @php
            $missingData = [];
            if (\App\Models\Product::count() === 0) $missingData[] = 'Produk';
            if (\App\Models\Warehouse::count() === 0) $missingData[] = 'Gudang';
        @endphp
        <div>
            <h3 class="text-lg font-medium text-slate-900">Aktivitas Terbaru</h3>
            <p class="text-sm text-slate-500">Log transaksi masuk dan keluar.</p>
        </div>
        <div class="flex gap-2">
            @if(count($missingData) > 0)
                <span class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium opacity-50 cursor-not-allowed bg-slate-900 text-slate-50 shadow h-9 px-4 py-2">
                    Transaksi Baru
                </span>
            @else
                <a href="{{ route('transactions.create') }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-slate-950 bg-slate-900 text-slate-50 shadow hover:bg-slate-900/90 h-9 px-4 py-2">
                    Transaksi Baru
                </a>
            @endif
        </div>
    </div>

    @if(count($missingData) > 0)
        <div class="p-4 mb-4 text-sm text-amber-800 rounded-lg bg-amber-50 border border-amber-200">
            <span class="font-semibold">Perhatian:</span> Anda belum dapat membuat transaksi. Lengkapi data berikut terlebih dahulu: <strong>{{ implode(', ', $missingData) }}</strong>.
        </div>
    @endif

    <x-card class="p-0 overflow-hidden">
        <x-table>
            <x-slot name="header">
                <th class="h-10 px-4 text-left font-medium">Tanggal</th>
                <th class="h-10 px-4 text-left font-medium">Tipe</th>
                <th class="h-10 px-4 text-left font-medium">Produk</th>
                <th class="h-10 px-4 text-left font-medium">Kuantitas</th>
                <th class="h-10 px-4 text-left font-medium">Gudang</th>
                <th class="h-10 px-4 text-left font-medium">Catatan</th>
            </x-slot>
            
            @forelse($transactions as $trx)
            <tr class="border-b transition-colors hover:bg-slate-50/50">
                <td class="p-4 text-sm text-slate-500">{{ $trx->created_at->format('M d, Y H:i') }}</td>
                <td class="p-4">
                    @if($trx->type === 'in')
                        <x-badge class="bg-emerald-100 text-emerald-800">Masuk</x-badge>
                    @else
                        <x-badge variant="destructive">Keluar</x-badge>
                    @endif
                </td>
                <td class="p-4 font-medium">{{ $trx->product->name ?? 'Unknown' }}</td>
                <td class="p-4">{{ $trx->quantity }}</td>
                <td class="p-4 text-slate-500">{{ $trx->warehouse->name ?? 'Unknown' }}</td>
                <td class="p-4 text-slate-500">{{ $trx->notes ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="p-4 text-center text-slate-500">Tidak ada transaksi yang ditemukan.</td>
            </tr>
            @endforelse
        </x-table>
    </x-card>
</x-app-layout>
