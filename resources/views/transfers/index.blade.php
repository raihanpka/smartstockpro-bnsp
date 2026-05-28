<x-app-layout>
    <x-slot name="header">
        Transfer Gudang
    </x-slot>

    <div class="mb-4 flex justify-between items-center">
        @php
            $missingData = [];
            if (\App\Models\Product::count() === 0) $missingData[] = 'Produk';
            if (\App\Models\Warehouse::count() < 2) $missingData[] = 'Minimal 2 Gudang';
        @endphp
        <div>
            <h3 class="text-lg font-medium text-slate-900">Transfer Persetujuan</h3>
            <p class="text-sm text-slate-500">Kelola pemindahan stok antar gudang.</p>
        </div>
        @if(count($missingData) > 0)
            <span class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium opacity-50 cursor-not-allowed bg-slate-900 text-slate-50 shadow h-9 px-4 py-2">
                Buat Pengajuan
            </span>
        @else
            <a href="{{ route('transfers.create') }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-slate-950 bg-slate-900 text-slate-50 shadow hover:bg-slate-900/90 h-9 px-4 py-2">
                Buat Pengajuan
            </a>
        @endif
    </div>

    @if(count($missingData) > 0)
        <div class="p-4 mb-4 text-sm text-amber-800 rounded-lg bg-amber-50 border border-amber-200">
            <span class="font-semibold flex items-center gap-1.5 mb-1 text-amber-900">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                Perhatian: Persyaratan Transfer Belum Lengkap!
            </span>
            <span>Anda belum dapat mengajukan transfer stok. Lengkapi data berikut terlebih dahulu:</span>
            <div class="flex gap-3 mt-2 font-semibold">
                @if(\App\Models\Product::count() === 0)
                    <a href="{{ route('products.index') }}" class="inline-flex items-center gap-1 text-xs text-amber-900 bg-amber-100/80 px-2 py-1 rounded hover:bg-amber-100">
                        + Tambah Produk
                    </a>
                @endif
                @if(\App\Models\Warehouse::count() < 2)
                    <a href="{{ route('warehouses.index') }}" class="inline-flex items-center gap-1 text-xs text-amber-900 bg-amber-100/80 px-2 py-1 rounded hover:bg-amber-100">
                        + Tambah Gudang (Minimal 2)
                    </a>
                @endif
            </div>
        </div>
    @endif

    @if(session('success'))
        <div class="p-4 mb-4 text-sm text-emerald-800 rounded-lg bg-emerald-50 border border-emerald-200">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200">
            {{ session('error') }}
        </div>
    @endif

    <x-card class="p-0 overflow-hidden">
        <x-table>
            <x-slot name="header">
                <th class="h-10 px-4 text-left font-medium">Tanggal</th>
                <th class="h-10 px-4 text-left font-medium">Produk</th>
                <th class="h-10 px-4 text-left font-medium">Kuantitas</th>
                <th class="h-10 px-4 text-left font-medium">Rute</th>
                <th class="h-10 px-4 text-left font-medium">Status</th>
                <th class="h-10 px-4 text-right font-medium">Aksi</th>
            </x-slot>
            
            @forelse($transfers as $tf)
            <tr class="border-b transition-colors hover:bg-slate-50/50">
                <td class="p-4 text-sm text-slate-500">{{ $tf->created_at->format('d M Y') }}</td>
                <td class="p-4 font-medium">{{ $tf->product->name }}</td>
                <td class="p-4">{{ $tf->quantity }}</td>
                <td class="p-4 text-sm text-slate-500">{{ $tf->sourceWarehouse->code }} &rarr; {{ $tf->destinationWarehouse->code }}</td>
                <td class="p-4">
                    @if($tf->status === 'pending')
                        <x-badge variant="outline" class="text-orange-500 border-orange-200">Menunggu</x-badge>
                    @elseif($tf->status === 'approved')
                        <x-badge variant="default" class="bg-emerald-500">Disetujui</x-badge>
                    @else
                        <x-badge variant="destructive">Ditolak</x-badge>
                    @endif
                </td>
                <td class="p-4 text-right">
                    @if($tf->status === 'pending' && in_array(Auth::user()->role, ['admin', 'manager']))
                    <div class="flex gap-2 justify-end">
                        <form method="POST" action="{{ route('transfers.update', $tf) }}">
                            @csrf @method('PUT')
                            <input type="hidden" name="status" value="approved">
                            <button type="submit" class="text-emerald-600 hover:underline text-sm font-medium">Setujui</button>
                        </form>
                        <form method="POST" action="{{ route('transfers.update', $tf) }}">
                            @csrf @method('PUT')
                            <input type="hidden" name="status" value="rejected">
                            <button type="submit" class="text-red-600 hover:underline text-sm font-medium">Tolak</button>
                        </form>
                    </div>
                    @else
                    <span class="text-slate-400 text-sm">Tidak Ada Tindakan</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="p-4 text-center text-slate-500">Tidak ada pengajuan transfer ditemukan.</td>
            </tr>
            @endforelse
        </x-table>
    </x-card>
</x-app-layout>
