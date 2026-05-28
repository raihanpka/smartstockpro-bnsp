<x-app-layout>
    <x-slot name="header">
        Tambah Produk
    </x-slot>

    <div class="mb-4">
        <h3 class="text-xl font-medium text-slate-900">Tambah Produk Baru</h3>
        <p class="text-base text-slate-500">Masukkan rincian produk baru ke dalam inventaris.</p>
    </div>

    <x-card class="max-w-2xl p-6">
        <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="name" value="Nama Produk" class="text-sm font-semibold text-slate-700" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full text-sm h-10 pl-3" :value="old('name')" required autofocus />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>
                <div>
                    <x-input-label for="sku" value="SKU" class="text-sm font-semibold text-slate-700" />
                    <x-text-input id="sku" name="sku" type="text" class="mt-1 block w-full text-sm h-10 pl-3" :value="old('sku')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('sku')" />
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="category_id" value="Kategori" class="text-sm font-semibold text-slate-700" />
                    <select id="category_id" name="category_id" class="mt-1 block w-full text-sm h-10 rounded-md border border-slate-200 bg-white pl-3 pr-10 shadow-sm focus:border-slate-400 focus:ring-0" required>
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('category_id')" />
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

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="min_stock" value="Level Stok Minimum" class="text-sm font-semibold text-slate-700" />
                    <x-text-input id="min_stock" name="min_stock" type="number" min="0" class="mt-1 block w-full text-sm h-10 pl-3" :value="old('min_stock', 10)" required />
                    <x-input-error class="mt-2" :messages="$errors->get('min_stock')" />
                </div>
                <div>
                    <x-input-label for="image" value="Gambar Produk (Opsional)" class="text-sm font-semibold text-slate-700" />
                    <input type="file" name="image" id="image" class="mt-1 block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-slate-50 file:text-slate-700 hover:file:bg-slate-100" accept="image/*">
                    <x-input-error class="mt-2" :messages="$errors->get('image')" />
                </div>
            </div>

            <div class="flex items-center gap-4">
                <x-primary-button class="h-10 text-sm">Simpan Produk</x-primary-button>
                <a href="{{ route('products.index') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-900 transition-colors">Batal</a>
            </div>
        </form>
    </x-card>
</x-app-layout>
