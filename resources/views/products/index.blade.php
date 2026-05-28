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

    {{-- ── Search & Filter Bar ── --}}
    <x-card class="p-4 mb-4">
        <form method="GET" action="{{ route('products.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 items-end">
            {{-- Pencarian (lebar 2 kolom pada layar sedang/besar) --}}
            <div class="sm:col-span-2">
                <label class="text-xs font-semibold text-slate-600 block mb-1">Cari Nama / SKU</label>
                <x-text-input name="search" type="search" class="w-full h-9 text-sm pl-3"
                    placeholder="Ketik nama atau SKU..." value="{{ request('search') }}" />
            </div>
            {{-- Filter Gudang --}}
            <div>
                <label class="text-xs font-semibold text-slate-600 block mb-1">Gudang</label>
                <select name="warehouse_id" class="h-9 rounded-md border border-slate-200 bg-white pl-3 pr-10 text-sm w-full focus:border-slate-400 focus:ring-0">
                    <option value="">Semua Gudang</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>
            {{-- Filter Kategori --}}
            <div>
                <label class="text-xs font-semibold text-slate-600 block mb-1">Kategori</label>
                <select name="category_id" class="h-9 rounded-md border border-slate-200 bg-white pl-3 pr-10 text-sm w-full focus:border-slate-400 focus:ring-0">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            {{-- Sorting --}}
            <div>
                <label class="text-xs font-semibold text-slate-600 block mb-1">Urutkan</label>
                <select name="sort" class="h-9 rounded-md border border-slate-200 bg-white pl-3 pr-10 text-sm w-full focus:border-slate-400 focus:ring-0">
                    <option value="created_at" {{ request('sort') === 'created_at' ? 'selected' : '' }}>Terbaru</option>
                    <option value="name"       {{ request('sort') === 'name'       ? 'selected' : '' }}>Nama A-Z</option>
                    <option value="stock"      {{ request('sort') === 'stock'      ? 'selected' : '' }}>Stok</option>
                    <option value="sku"        {{ request('sort') === 'sku'        ? 'selected' : '' }}>SKU</option>
                </select>
            </div>
            {{-- Arah --}}
            <div>
                <label class="text-xs font-semibold text-slate-600 block mb-1">Arah</label>
                <select name="dir" class="h-9 rounded-md border border-slate-200 bg-white pl-3 pr-10 text-sm w-full focus:border-slate-400 focus:ring-0">
                    <option value="desc" {{ request('dir', 'desc') === 'desc' ? 'selected' : '' }}>Turun ↓</option>
                    <option value="asc"  {{ request('dir') === 'asc'          ? 'selected' : '' }}>Naik ↑</option>
                </select>
            </div>
            
            {{-- Baris bawah: Checkbox (kiri) & Buttons (kanan) --}}
            <div class="sm:col-span-2 md:col-span-3 lg:col-span-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pt-3 border-t border-slate-100">
                <div class="flex items-center h-9">
                    <label class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer select-none">
                        <input type="checkbox" name="low_stock" value="1" class="rounded border-slate-300 text-slate-900 focus:ring-0"
                            {{ request('low_stock') === '1' ? 'checked' : '' }}>
                        Stok Kritis Saja
                    </label>
                </div>
                <div class="flex gap-2">
                    <x-primary-button type="submit" class="h-9">Filter</x-primary-button>
                    <a href="{{ route('products.index') }}" class="inline-flex items-center justify-center h-9 px-4 rounded-md border border-slate-200 text-sm text-slate-600 hover:bg-slate-50 transition-colors">Reset</a>
                </div>
            </div>
        </form>
    </x-card>

    @if(count($missingMasterData) > 0)
        <div class="p-4 mb-4 text-sm text-amber-800 rounded-lg bg-amber-50 border border-amber-200">
            <span class="font-semibold flex items-center gap-1.5 mb-1 text-amber-900">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                Perhatian: Master Data Belum Lengkap!
            </span>
            <span>Anda tidak dapat menambahkan atau mengimpor produk. Silakan lengkapi master data berikut terlebih dahulu:</span>
            <div class="flex gap-3 mt-2 font-semibold">
                @if(\App\Models\Category::count() === 0)
                    <a href="{{ route('categories.index') }}" class="inline-flex items-center gap-1 text-xs text-amber-900 bg-amber-100/80 px-2 py-1 rounded hover:bg-amber-100">
                        + Tambah Kategori
                    </a>
                @endif
                @if(\App\Models\Warehouse::count() === 0)
                    <a href="{{ route('warehouses.index') }}" class="inline-flex items-center gap-1 text-xs text-amber-900 bg-amber-100/80 px-2 py-1 rounded hover:bg-amber-100">
                        + Tambah Gudang
                    </a>
                @endif
                @if(\App\Models\Supplier::count() === 0)
                    <a href="{{ route('suppliers.index') }}" class="inline-flex items-center gap-1 text-xs text-amber-900 bg-amber-100/80 px-2 py-1 rounded hover:bg-amber-100">
                        + Tambah Pemasok
                    </a>
                @endif
            </div>
        </div>
    @endif

    <x-card class="p-0 overflow-hidden">
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
                <td colspan="7" class="p-4 text-center text-slate-500">Tidak ada produk.</td>
            </tr>
            @endforelse
        </x-table>
    </x-card>

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
