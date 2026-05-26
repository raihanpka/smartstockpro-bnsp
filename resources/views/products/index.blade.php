<x-app-layout>
    <x-slot name="header">
        Inventaris Produk
    </x-slot>

    <div class="mb-4 flex justify-between items-center">
        @php
            $missingMasterData = [];
            if (\App\Models\Category::count() === 0) $missingMasterData[] = 'Kategori';
            if (\App\Models\Warehouse::count() === 0) $missingMasterData[] = 'Gudang';
            if (\App\Models\Supplier::count() === 0) $missingMasterData[] = 'Pemasok';
        @endphp
        <div>
            <h3 class="text-lg font-medium text-slate-900">Semua Produk</h3>
            <p class="text-sm text-slate-500">Kelola inventaris produk di seluruh gudang.</p>
        </div>
        <div class="flex gap-2">
            <form method="POST" action="{{ route('products.export') }}">
                @csrf
                <x-secondary-button type="submit" class="bg-white">Ekspor PDF</x-secondary-button>
            </form>
            @if(count($missingMasterData) > 0)
                <span class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium opacity-50 cursor-not-allowed bg-white border border-slate-200 text-slate-900 h-9 px-4 py-2">Impor Excel</span>
                <span class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium opacity-50 cursor-not-allowed bg-slate-900 text-slate-50 shadow h-9 px-4 py-2">Tambah Produk</span>
            @else
                <x-secondary-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'import-products')">Impor Excel</x-secondary-button>
                <a href="{{ route('products.create') }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-slate-950 bg-slate-900 text-slate-50 shadow hover:bg-slate-900/90 h-9 px-4 py-2">
                    Tambah Produk
                </a>
            @endif
        </div>
    </div>

    @if(count($missingMasterData) > 0)
        <div class="p-4 mb-4 text-sm text-amber-800 rounded-lg bg-amber-50 border border-amber-200">
            <span class="font-semibold">Perhatian:</span> Anda tidak dapat menambahkan atau mengimpor produk. Silakan lengkapi master data berikut di halaman <a href="{{ route('catalog.index') }}" class="font-bold underline hover:text-amber-900">Katalog Produk</a> terlebih dahulu: <strong>{{ implode(', ', $missingMasterData) }}</strong>.
        </div>
    @endif

    <x-table>
        <x-slot name="header">
            <th class="h-10 px-4 text-left align-middle font-medium w-16">Gambar</th>
            <th class="h-10 px-4 text-left align-middle font-medium">SKU</th>
            <th class="h-10 px-4 text-left align-middle font-medium">Nama</th>
            <th class="h-10 px-4 text-left align-middle font-medium">Stok</th>
            <th class="h-10 px-4 text-left align-middle font-medium">Status</th>
            <th class="h-10 px-4 text-left align-middle font-medium">Gudang</th>
            <th class="h-10 px-4 text-right align-middle font-medium">Aksi</th>
        </x-slot>
        
        @forelse($products as $product)
        <tr class="border-b transition-colors hover:bg-slate-50/50">
            <td class="p-4 align-middle">
                @if($product->image_path)
                    <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}" class="w-10 h-10 rounded-md object-cover border border-slate-200">
                @else
                    <div class="w-10 h-10 rounded-md bg-slate-100 border border-slate-200 flex items-center justify-center">
                        <span class="text-xs text-slate-400">No Img</span>
                    </div>
                @endif
            </td>
            <td class="p-4 align-middle font-medium">{{ $product->sku }}</td>
            <td class="p-4 align-middle">{{ $product->name }}</td>
            <td class="p-4 align-middle">{{ $product->stock }}</td>
            <td class="p-4 align-middle">
                @if($product->stock < $product->min_stock)
                    <x-badge variant="destructive">Stok Kritis</x-badge>
                @else
                    <x-badge variant="secondary">Tersedia</x-badge>
                @endif
            </td>
            <td class="p-4 align-middle">{{ $product->warehouse->name ?? 'N/A' }}</td>
            <td class="p-4 align-middle text-right">
                <a href="{{ route('products.edit', $product) }}" class="text-slate-500 hover:text-slate-900 font-medium text-sm">Ubah</a>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="p-4 text-center text-slate-500">Tidak ada produk.</td>
        </tr>
        @endforelse
    </x-table>
    
    <div class="mt-4">
        {{ $products->links() }}
    </div>

    <x-modal name="import-products" focusable>
        <form method="POST" action="{{ route('products.import') }}" enctype="multipart/form-data" class="p-6">
            @csrf
            <h2 class="text-lg font-medium text-slate-900">Impor Produk (Excel/CSV)</h2>

            <div class="mt-4">
                <x-input-label for="warehouse_id" value="Gudang Tujuan" />
                <select id="warehouse_id" name="warehouse_id" class="mt-1 block w-full rounded-md border border-slate-200" required>
                    @foreach(\App\Models\Warehouse::all() as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mt-4">
                <x-input-label for="file" value="Unggah File" />
                <input type="file" name="file" id="file" class="mt-1 block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-slate-50 file:text-slate-700 hover:file:bg-slate-100" required>
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">Batal</x-secondary-button>
                <x-primary-button class="ms-3">Mulai Impor</x-primary-button>
            </div>
        </form>
    </x-modal>
</x-app-layout>
