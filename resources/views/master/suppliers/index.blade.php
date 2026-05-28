<x-app-layout>
    <x-slot name="header">
        Kelola Pemasok
    </x-slot>

    <div class="mb-4 flex justify-between items-center">
        <div>
            <h3 class="text-lg font-medium text-slate-900">Kelola Pemasok (Supplier)</h3>
            <p class="text-sm text-slate-500">Kelola daftar pemasok barang untuk kebutuhan inventaris.</p>
        </div>
        <x-primary-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'create-supplier')">Tambah Pemasok</x-primary-button>
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

    <x-card class="p-0 overflow-hidden">
        <x-table>
            <x-slot name="header">
                <th class="h-10 px-4 text-left font-medium">Nama Pemasok</th>
                <th class="h-10 px-4 text-left font-medium">Narahubung</th>
                <th class="h-10 px-4 text-left font-medium">Kontak</th>
                <th class="h-10 px-4 text-left font-medium">Alamat</th>
                <th class="h-10 px-4 text-right font-medium">Aksi</th>
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
                <td class="p-4 text-right">
                    <button x-data @click="$dispatch('open-modal', 'edit-supplier-{{ $supplier->id }}')"
                        class="text-slate-500 hover:text-slate-900 font-medium text-sm mr-2">Edit</button>
                    <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-700 font-medium text-sm"
                            onclick="return confirm('Hapus pemasok ini?')">Hapus</button>
                    </form>
                </td>
            </tr>
            {{-- Edit Modal per supplier --}}
            <x-modal name="edit-supplier-{{ $supplier->id }}" focusable>
                <form method="POST" action="{{ route('suppliers.update', $supplier) }}" class="p-6">
                    @csrf @method('PUT')
                    <h2 class="text-lg font-medium text-slate-900">Edit Pemasok</h2>
                    <div class="mt-4">
                        <x-input-label value="Nama Pemasok" />
                        <x-text-input name="name" type="text" class="mt-1 block w-full"
                            value="{{ $supplier->name }}" required />
                    </div>
                    <div class="mt-4">
                        <x-input-label value="Narahubung" />
                        <x-text-input name="contact_person" type="text" class="mt-1 block w-full"
                            value="{{ $supplier->contact_person }}" />
                    </div>
                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <div>
                            <x-input-label value="Telepon" />
                            <x-text-input name="phone" type="text" class="mt-1 block w-full"
                                value="{{ $supplier->phone }}" />
                        </div>
                        <div>
                            <x-input-label value="Email" />
                            <x-text-input name="email" type="email" class="mt-1 block w-full"
                                value="{{ $supplier->email }}" />
                        </div>
                    </div>
                    <div class="mt-4">
                        <x-input-label value="Alamat" />
                        <textarea name="address" rows="2"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ $supplier->address }}</textarea>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <x-secondary-button x-on:click="$dispatch('close')">Batal</x-secondary-button>
                        <x-primary-button class="ms-3">Simpan</x-primary-button>
                    </div>
                </form>
            </x-modal>
            @empty
            <tr>
                <td colspan="5" class="p-4 text-center text-slate-500">Tidak ada pemasok.</td>
            </tr>
            @endforelse
        </x-table>
    </x-card>

    <div class="mt-4">
        {{ $suppliers->links() }}
    </div>

    <!-- Create Modal -->
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
