<x-app-layout>
    <x-slot name="header">
        Kelola Gudang
    </x-slot>

    <div class="mb-4 flex justify-between items-center">
        <div>
            <h3 class="text-lg font-medium text-slate-900">Kelola Gudang (Warehouse)</h3>
            <p class="text-sm text-slate-500">Kelola lokasi gudang penyimpanan barang dan pantau lokasi via peta interaktif.</p>
        </div>
        <x-primary-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'create-warehouse')">Tambah Gudang</x-primary-button>
    </div>

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

    {{-- Peta Interaktif Leaflet --}}
    @php
        $mappableWarehouses = $warehouses->filter(fn($w) => $w->latitude && $w->longitude);
    @endphp

    <x-card class="p-4 mb-6">
        <h4 class="text-sm font-semibold text-slate-700 mb-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Peta Lokasi Gudang (Leaflet Map)
        </h4>
        @if($mappableWarehouses->count() > 0)
            <div id="warehouse-map" style="height: 380px; border-radius: 8px; z-index: 0;" class="border border-slate-200"></div>
        @else
            <div class="h-48 rounded-lg bg-slate-50 border border-dashed border-slate-300 flex flex-col items-center justify-center text-center p-4">
                <svg class="w-10 h-10 text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                </svg>
                <p class="text-sm font-medium text-slate-600">Tidak ada gudang dengan koordinat Latitude & Longitude.</p>
                <p class="text-xs text-slate-400 mt-1">Silakan edit gudang dan masukkan koordinat untuk menampilkan peta.</p>
            </div>
        @endif
    </x-card>

    <x-card class="p-0 overflow-hidden">
        <x-table>
            <x-slot name="header">
                <th class="h-10 px-4 text-left font-medium">Kode</th>
                <th class="h-10 px-4 text-left font-medium">Nama Gudang</th>
                <th class="h-10 px-4 text-left font-medium">Alamat</th>
                <th class="h-10 px-4 text-left font-medium">Koordinat (Lat, Lng)</th>
                <th class="h-10 px-4 text-right font-medium">Aksi</th>
            </x-slot>
            @forelse($warehouses as $warehouse)
            <tr class="border-b transition-colors hover:bg-slate-50/50">
                <td class="p-4 font-medium">{{ $warehouse->code }}</td>
                <td class="p-4">{{ $warehouse->name }}</td>
                <td class="p-4 text-slate-500">{{ $warehouse->address ?? '-' }}</td>
                <td class="p-4 text-slate-500">
                    @if($warehouse->latitude && $warehouse->longitude)
                        <span class="inline-flex items-center gap-1 text-slate-700 bg-slate-100 px-2 py-0.5 rounded text-xs">
                            {{ $warehouse->latitude }}, {{ $warehouse->longitude }}
                        </span>
                    @else
                        <span class="text-xs text-slate-400 font-normal">Belum diatur</span>
                    @endif
                </td>
                <td class="p-4 text-right">
                    <button x-data @click="$dispatch('open-modal', 'edit-warehouse-{{ $warehouse->id }}')"
                        class="text-slate-500 hover:text-slate-900 font-medium text-sm mr-2">Edit</button>
                    <form method="POST" action="{{ route('warehouses.destroy', $warehouse) }}" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-700 font-medium text-sm"
                            onclick="return confirm('Hapus gudang ini?')">Hapus</button>
                    </form>
                </td>
            </tr>
            {{-- Edit Modal per warehouse --}}
            <x-modal name="edit-warehouse-{{ $warehouse->id }}" focusable>
                <form method="POST" action="{{ route('warehouses.update', $warehouse) }}" class="p-6">
                    @csrf @method('PUT')
                    <h2 class="text-lg font-medium text-slate-900">Edit Gudang</h2>
                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <div>
                            <x-input-label value="Kode Gudang" />
                            <x-text-input name="code" type="text" class="mt-1 block w-full"
                                value="{{ $warehouse->code }}" required />
                        </div>
                        <div>
                            <x-input-label value="Nama Gudang" />
                            <x-text-input name="name" type="text" class="mt-1 block w-full"
                                value="{{ $warehouse->name }}" required />
                        </div>
                    </div>
                    <div class="mt-4">
                        <x-input-label value="Alamat" />
                        <textarea name="address" rows="2"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ $warehouse->address }}</textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <div>
                            <x-input-label value="Latitude" />
                            <x-text-input name="latitude" type="number" step="any" class="mt-1 block w-full"
                                value="{{ $warehouse->latitude }}" placeholder="-6.2088" />
                            <span class="text-[10px] text-slate-500 mt-1 block">Gunakan tanda minus (-) untuk Lintang Selatan (LS).</span>
                        </div>
                        <div>
                            <x-input-label value="Longitude" />
                            <x-text-input name="longitude" type="number" step="any" class="mt-1 block w-full"
                                value="{{ $warehouse->longitude }}" placeholder="106.8456" />
                            <span class="text-[10px] text-slate-500 mt-1 block">Gunakan nilai positif untuk Bujur Timur (BT).</span>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <x-secondary-button x-on:click="$dispatch('close')">Batal</x-secondary-button>
                        <x-primary-button class="ms-3">Simpan</x-primary-button>
                    </div>
                </form>
            </x-modal>
            @empty
            <tr>
                <td colspan="5" class="p-4 text-center text-slate-500">Tidak ada gudang.</td>
            </tr>
            @endforelse
        </x-table>
    </x-card>

    <div class="mt-4">
        {{ $warehouses->links() }}
    </div>

    <!-- Create Modal -->
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
                    <x-text-input id="wh_lat" name="latitude" type="number" step="any" class="mt-1 block w-full" placeholder="-6.2088" />
                    <span class="text-[10px] text-slate-500 mt-1 block">Gunakan tanda minus (-) untuk Lintang Selatan (LS).</span>
                </div>
                <div>
                    <x-input-label for="wh_lng" value="Longitude (Opsional)" />
                    <x-text-input id="wh_lng" name="longitude" type="number" step="any" class="mt-1 block w-full" placeholder="106.8456" />
                    <span class="text-[10px] text-slate-500 mt-1 block">Gunakan nilai positif untuk Bujur Timur (BT).</span>
                </div>
            </div>
            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">Batal</x-secondary-button>
                <x-primary-button class="ms-3">Simpan</x-primary-button>
            </div>
        </form>
    </x-modal>

    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @endpush

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    @if($mappableWarehouses->count() > 0)
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const warehousesData = {!! json_encode($mappableWarehouses->map(fn($w) => [
                'name'      => $w->name,
                'code'      => $w->code,
                'address'   => $w->address ?? '',
                'lat'       => (float) $w->latitude,
                'lng'       => (float) $w->longitude,
                'products'  => $w->products()->count(),
            ])->values()) !!};

            const firstCoord = [warehousesData[0].lat, warehousesData[0].lng];
            const map = L.map('warehouse-map').setView(firstCoord, 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            const bounds = [];
            warehousesData.forEach(function (wh) {
                const marker = L.marker([wh.lat, wh.lng]).addTo(map);
                marker.bindPopup(
                    '<strong>' + wh.name + '</strong> (' + wh.code + ')<br>' +
                    (wh.address ? wh.address + '<br>' : '') +
                    'Total Produk: ' + wh.products
                );
                bounds.push([wh.lat, wh.lng]);
            });

            if (bounds.length > 1) {
                map.fitBounds(bounds, { padding: [40, 40] });
            }
        });
    </script>
    @endif
    @endpush
</x-app-layout>
