<x-app-layout>
    <x-slot name="header">
        Pengajuan Transfer Stok
    </x-slot>

    <div class="mb-4">
        <h3 class="text-xl font-medium text-slate-900">Pengajuan Transfer Baru</h3>
        <p class="text-base text-slate-500">Ajukan perpindahan barang antar gudang.</p>
    </div>

    @if(session('error'))
        <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200">
            {{ session('error') }}
        </div>
    @endif

    <x-card class="max-w-2xl p-6">
        <form method="POST" action="{{ route('transfers.store') }}" class="space-y-6">
            @csrf

            <div>
                <x-input-label for="product_id" value="Produk" class="text-sm font-semibold text-slate-700" />
                <select id="product_id" name="product_id" class="mt-1 block w-full text-sm h-10 rounded-md border border-slate-200 bg-white pl-3 pr-10 shadow-sm focus:border-slate-400 focus:ring-0" required>
                    <option value="">-- Pilih Produk --</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }} (Stok: {{ $product->stock }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="source_warehouse_id" value="Dari Gudang (Asal)" class="text-sm font-semibold text-slate-700" />
                    <select id="source_warehouse_id" name="source_warehouse_id" class="mt-1 block w-full text-sm h-10 rounded-md border border-slate-200 bg-white pl-3 pr-10 shadow-sm focus:border-slate-400 focus:ring-0" required>
                        <option value="">-- Pilih Gudang Asal --</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="destination_warehouse_id" value="Ke Gudang (Tujuan)" class="text-sm font-semibold text-slate-700" />
                    <select id="destination_warehouse_id" name="destination_warehouse_id" class="mt-1 block w-full text-sm h-10 rounded-md border border-slate-200 bg-white pl-3 pr-10 shadow-sm focus:border-slate-400 focus:ring-0" required>
                        <option value="">-- Pilih Gudang Tujuan --</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <x-input-label for="quantity" value="Kuantitas Transfer" class="text-sm font-semibold text-slate-700" />
                <x-text-input id="quantity" name="quantity" type="number" min="1" class="mt-1 block w-full text-sm h-10 pl-3" required />
            </div>

            <div class="flex items-center gap-4">
                <x-primary-button class="h-10 text-sm">Kirim Pengajuan</x-primary-button>
                <a href="{{ route('transfers.index') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-900 transition-colors">Batal</a>
            </div>
        </form>
    </x-card>
</x-app-layout>
