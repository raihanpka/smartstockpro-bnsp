<x-app-layout>
    <x-slot name="header">
        Kelola Katalog Produk
    </x-slot>

    <div class="mb-4">
        <h3 class="text-lg font-medium text-slate-900">Kelola Katalog Produk</h3>
        <p class="text-sm text-slate-500">Kelola master data untuk Kategori, Gudang, dan Pemasok dalam satu tempat terpadu.</p>
    </div>

    @if(session('success'))
        <div class="p-4 mb-4 text-sm text-emerald-800 rounded-lg bg-emerald-50 border border-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    <div x-data="{ tab: 'categories' }">
        <!-- Tabs Navigation -->
        <div class="border-b border-slate-200 mb-6">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button @click="tab = 'categories'" 
                        :class="tab === 'categories' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    Kategori
                </button>
                <button @click="tab = 'warehouses'" 
                        :class="tab === 'warehouses' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    Gudang
                </button>
                <button @click="tab = 'suppliers'" 
                        :class="tab === 'suppliers' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    Pemasok
                </button>
            </nav>
        </div>

        <!-- Tab Content: Categories -->
        <div x-show="tab === 'categories'" x-transition>
            <div class="flex justify-end mb-4">
                <x-primary-button x-on:click.prevent="$dispatch('open-modal', 'create-category')">Tambah Kategori</x-primary-button>
            </div>
            <x-card class="p-0 overflow-hidden">
                <x-table>
                    <x-slot name="header">
                        <th class="h-10 px-4 text-left font-medium">Nama Kategori</th>
                        <th class="h-10 px-4 text-left font-medium">Deskripsi</th>
                        <th class="h-10 px-4 text-right font-medium">Total Produk</th>
                    </x-slot>
                    @forelse($categories as $category)
                    <tr class="border-b transition-colors hover:bg-slate-50/50">
                        <td class="p-4 font-medium">{{ $category->name }}</td>
                        <td class="p-4 text-slate-500">{{ $category->description ?? '-' }}</td>
                        <td class="p-4 text-right">{{ $category->products_count }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="p-4 text-center text-slate-500">Tidak ada kategori.</td>
                    </tr>
                    @endforelse
                </x-table>
            </x-card>
        </div>

        <!-- Tab Content: Warehouses -->
        <div x-show="tab === 'warehouses'" x-transition style="display: none;">
            <div class="flex justify-end mb-4">
                <x-primary-button x-on:click.prevent="$dispatch('open-modal', 'create-warehouse')">Tambah Gudang</x-primary-button>
            </div>
            <x-card class="p-0 overflow-hidden">
                <x-table>
                    <x-slot name="header">
                        <th class="h-10 px-4 text-left font-medium">Kode</th>
                        <th class="h-10 px-4 text-left font-medium">Nama Gudang</th>
                        <th class="h-10 px-4 text-left font-medium">Alamat</th>
                        <th class="h-10 px-4 text-left font-medium">Koordinat (Lat, Lng)</th>
                    </x-slot>
                    @forelse($warehouses as $warehouse)
                    <tr class="border-b transition-colors hover:bg-slate-50/50">
                        <td class="p-4 font-medium">{{ $warehouse->code }}</td>
                        <td class="p-4">{{ $warehouse->name }}</td>
                        <td class="p-4 text-slate-500">{{ $warehouse->address ?? '-' }}</td>
                        <td class="p-4 text-slate-500">{{ $warehouse->latitude ?? '-' }}, {{ $warehouse->longitude ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-4 text-center text-slate-500">Tidak ada gudang.</td>
                    </tr>
                    @endforelse
                </x-table>
            </x-card>
        </div>

        <!-- Tab Content: Suppliers -->
        <div x-show="tab === 'suppliers'" x-transition style="display: none;">
            <div class="flex justify-end mb-4">
                <x-primary-button x-on:click.prevent="$dispatch('open-modal', 'create-supplier')">Tambah Pemasok</x-primary-button>
            </div>
            <x-card class="p-0 overflow-hidden">
                <x-table>
                    <x-slot name="header">
                        <th class="h-10 px-4 text-left font-medium">Nama Pemasok</th>
                        <th class="h-10 px-4 text-left font-medium">Narahubung</th>
                        <th class="h-10 px-4 text-left font-medium">Kontak</th>
                        <th class="h-10 px-4 text-left font-medium">Alamat</th>
                    </x-slot>
                    @forelse($suppliers as $supplier)
                    <tr class="border-b transition-colors hover:bg-slate-50/50">
                        <td class="p-4 font-medium">{{ $supplier->name }}</td>
                        <td class="p-4">{{ $supplier->contact_person ?? '-' }}</td>
                        <td class="p-4 text-slate-500">
                            <div>{{ $supplier->phone ?? '-' }}</div>
                            <div class="text-xs">{{ $supplier->email ?? '-' }}</div>
                        </td>
                        <td class="p-4 text-slate-500">{{ $supplier->address ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-4 text-center text-slate-500">Tidak ada pemasok.</td>
                    </tr>
                    @endforelse
                </x-table>
            </x-card>
        </div>
    </div>

    <!-- Modals -->
    <!-- Modal Kategori -->
    <x-modal name="create-category" focusable>
        <form method="POST" action="{{ route('categories.store') }}" class="p-6">
            @csrf
            <h2 class="text-lg font-medium text-slate-900">Tambah Kategori</h2>
            <div class="mt-4">
                <x-input-label for="cat_name" value="Nama Kategori" />
                <x-text-input id="cat_name" name="name" type="text" class="mt-1 block w-full" required />
            </div>
            <div class="mt-4">
                <x-input-label for="cat_desc" value="Deskripsi" />
                <textarea id="cat_desc" name="description" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
            </div>
            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">Batal</x-secondary-button>
                <x-primary-button class="ms-3">Simpan</x-primary-button>
            </div>
        </form>
    </x-modal>

    <!-- Modal Gudang -->
    <x-modal name="create-warehouse" focusable>
        <form method="POST" action="{{ route('warehouses.store') }}" class="p-6">
            @csrf
            <h2 class="text-lg font-medium text-slate-900">Tambah Gudang</h2>
            <div class="grid grid-cols-2 gap-4 mt-4">
                <div>
                    <x-input-label for="wh_code" value="Kode Gudang" />
                    <x-text-input id="wh_code" name="code" type="text" class="mt-1 block w-full" placeholder="WH-01" required />
                </div>
                <div>
                    <x-input-label for="wh_name" value="Nama Gudang" />
                    <x-text-input id="wh_name" name="name" type="text" class="mt-1 block w-full" required />
                </div>
            </div>
            <div class="mt-4">
                <x-input-label for="wh_address" value="Alamat" />
                <textarea id="wh_address" name="address" rows="2" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4 mt-4">
                <div>
                    <x-input-label for="wh_lat" value="Latitude (Opsional)" />
                    <x-text-input id="wh_lat" name="latitude" type="number" step="any" class="mt-1 block w-full" />
                </div>
                <div>
                    <x-input-label for="wh_lng" value="Longitude (Opsional)" />
                    <x-text-input id="wh_lng" name="longitude" type="number" step="any" class="mt-1 block w-full" />
                </div>
            </div>
            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">Batal</x-secondary-button>
                <x-primary-button class="ms-3">Simpan</x-primary-button>
            </div>
        </form>
    </x-modal>

    <!-- Modal Pemasok -->
    <x-modal name="create-supplier" focusable>
        <form method="POST" action="{{ route('suppliers.store') }}" class="p-6">
            @csrf
            <h2 class="text-lg font-medium text-slate-900">Tambah Pemasok</h2>
            <div class="mt-4">
                <x-input-label for="sup_name" value="Nama Pemasok / Perusahaan" />
                <x-text-input id="sup_name" name="name" type="text" class="mt-1 block w-full" required />
            </div>
            <div class="mt-4">
                <x-input-label for="sup_contact" value="Narahubung (Contact Person)" />
                <x-text-input id="sup_contact" name="contact_person" type="text" class="mt-1 block w-full" />
            </div>
            <div class="grid grid-cols-2 gap-4 mt-4">
                <div>
                    <x-input-label for="sup_phone" value="Telepon" />
                    <x-text-input id="sup_phone" name="phone" type="text" class="mt-1 block w-full" />
                </div>
                <div>
                    <x-input-label for="sup_email" value="Email" />
                    <x-text-input id="sup_email" name="email" type="email" class="mt-1 block w-full" />
                </div>
            </div>
            <div class="mt-4">
                <x-input-label for="sup_address" value="Alamat" />
                <textarea id="sup_address" name="address" rows="2" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
            </div>
            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">Batal</x-secondary-button>
                <x-primary-button class="ms-3">Simpan</x-primary-button>
            </div>
        </form>
    </x-modal>
</x-app-layout>
