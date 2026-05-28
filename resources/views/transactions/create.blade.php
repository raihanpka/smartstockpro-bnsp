<x-app-layout>
    <x-slot name="header">
        Catat Transaksi
    </x-slot>

    <div class="mb-4">
        <h3 class="text-xl font-medium text-slate-900">Transaksi Baru</h3>
        <p class="text-base text-slate-500">Catat transaksi stok barang masuk atau keluar.</p>
    </div>

    @if(session('error'))
        <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <x-card class="max-w-2xl p-6">
        <form method="POST" action="{{ route('transactions.store') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="type" value="Tipe Transaksi" class="text-sm font-semibold text-slate-700" />
                    <select id="type" name="type" class="mt-1 block w-full text-sm h-10 rounded-md border border-slate-200 bg-white pl-3 pr-10 shadow-sm focus:border-slate-400 focus:ring-0" required>
                        <option value="in" {{ old('type') === 'in' ? 'selected' : '' }}>Inbound (Barang Masuk)</option>
                        <option value="out" {{ old('type') === 'out' ? 'selected' : '' }}>Outbound (Barang Keluar)</option>
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('type')" />
                </div>
                <div>
                    <x-input-label for="quantity" value="Kuantitas" class="text-sm font-semibold text-slate-700" />
                    <x-text-input id="quantity" name="quantity" type="number" min="1" class="mt-1 block w-full text-sm h-10 pl-3" :value="old('quantity', 1)" required />
                    <x-input-error class="mt-2" :messages="$errors->get('quantity')" />
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="product_id" value="Produk" class="text-sm font-semibold text-slate-700" />
                    <select id="product_id" name="product_id" class="mt-1 block w-full text-sm h-10 rounded-md border border-slate-200 bg-white pl-3 pr-10 shadow-sm focus:border-slate-400 focus:ring-0" required>
                        <option value="">-- Pilih Produk --</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>{{ $product->name }} ({{ $product->sku }})</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('product_id')" />
                </div>
                <div>
                    <x-input-label for="warehouse_id" value="Gudang" class="text-sm font-semibold text-slate-700" />
                    <select id="warehouse_id" name="warehouse_id" class="mt-1 block w-full text-sm h-10 rounded-md border border-slate-200 bg-white pl-3 pr-10 shadow-sm focus:border-slate-400 focus:ring-0" required>
                        <option value="">-- Pilih Gudang --</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('warehouse_id')" />
                </div>
            </div>

            <div>
                <x-input-label for="notes" value="Catatan (Opsional)" class="text-sm font-semibold text-slate-700" />
                <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full text-sm rounded-md border border-slate-200 bg-white px-3 py-2 shadow-sm focus:border-slate-400 focus:ring-0">{{ old('notes') }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('notes')" />
            </div>

            <div class="flex items-center gap-4">
                <x-primary-button class="h-10 text-sm">Simpan Transaksi</x-primary-button>
                <a href="{{ route('transactions.index') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-900 transition-colors">Batal</a>
            </div>
        </form>
    </x-card>
</x-app-layout>
