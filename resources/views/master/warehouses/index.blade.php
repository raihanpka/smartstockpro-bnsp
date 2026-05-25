<x-app-layout>
    <x-slot name="header">
        Warehouses Management
    </x-slot>

    <div class="mb-4 flex justify-between items-center">
        <div>
            <h3 class="text-lg font-medium text-slate-900">Master Data: Warehouses</h3>
            <p class="text-sm text-slate-500">Kelola daftar gudang yang tersedia.</p>
        </div>
        <x-primary-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'create-warehouse')">Add Warehouse</x-primary-button>
    </div>

    @if(session('success'))
        <div class="p-4 mb-4 text-sm text-emerald-800 rounded-lg bg-emerald-50 border border-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    <x-card class="p-0 overflow-hidden">
        <x-table>
            <x-slot name="header">
                <th class="h-10 px-4 text-left font-medium">Code</th>
                <th class="h-10 px-4 text-left font-medium">Name</th>
                <th class="h-10 px-4 text-left font-medium">Location</th>
                <th class="h-10 px-4 text-right font-medium">Action</th>
            </x-slot>
            
            @forelse($warehouses as $warehouse)
            <tr class="border-b transition-colors hover:bg-slate-50/50">
                <td class="p-4 font-medium">{{ $warehouse->code }}</td>
                <td class="p-4">{{ $warehouse->name }}</td>
                <td class="p-4 text-slate-500">{{ $warehouse->latitude ?? '-' }}, {{ $warehouse->longitude ?? '-' }}</td>
                <td class="p-4 text-right">
                    <button class="text-slate-500 hover:text-slate-900 font-medium text-sm">Edit</button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="p-4 text-center text-slate-500">No warehouses found.</td>
            </tr>
            @endforelse
        </x-table>
    </x-card>

    <x-modal name="create-warehouse" focusable>
        <form method="POST" action="{{ route('warehouses.store') }}" class="p-6">
            @csrf
            <h2 class="text-lg font-medium text-slate-900">
                Tambah Gudang Baru
            </h2>
            <p class="mt-1 text-sm text-slate-600">
                Silakan isi kode unik dan nama gudang.
            </p>

            <div class="mt-6">
                <x-input-label for="code" value="Warehouse Code" />
                <x-text-input id="code" name="code" type="text" class="mt-1 block w-full" placeholder="WH-01" required autofocus />
            </div>

            <div class="mt-6">
                <x-input-label for="name" value="Warehouse Name" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" placeholder="Gudang Pusat" required />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Cancel
                </x-secondary-button>
                <x-primary-button class="ms-3">
                    Save Warehouse
                </x-primary-button>
            </div>
        </form>
    </x-modal>
</x-app-layout>
