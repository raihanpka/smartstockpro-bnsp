<x-app-layout>
    <x-slot name="header">
        Suppliers Management
    </x-slot>

    <div class="mb-4 flex justify-between items-center">
        <div>
            <h3 class="text-lg font-medium text-slate-900">Master Data: Suppliers</h3>
            <p class="text-sm text-slate-500">Kelola daftar pemasok barang.</p>
        </div>
        <x-primary-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'create-supplier')">Add Supplier</x-primary-button>
    </div>

    @if(session('success'))
        <div class="p-4 mb-4 text-sm text-emerald-800 rounded-lg bg-emerald-50 border border-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    <x-card class="p-0 overflow-hidden">
        <x-table>
            <x-slot name="header">
                <th class="h-10 px-4 text-left font-medium">Name</th>
                <th class="h-10 px-4 text-left font-medium">Contact</th>
                <th class="h-10 px-4 text-left font-medium">Phone / Email</th>
            </x-slot>
            
            @forelse($suppliers as $supplier)
            <tr class="border-b transition-colors hover:bg-slate-50/50">
                <td class="p-4 font-medium">{{ $supplier->name }}</td>
                <td class="p-4">{{ $supplier->contact_person ?? '-' }}</td>
                <td class="p-4 text-slate-500">{{ $supplier->phone ?? '-' }} <br> {{ $supplier->email ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="p-4 text-center text-slate-500">No suppliers found.</td>
            </tr>
            @endforelse
        </x-table>
    </x-card>

    <x-modal name="create-supplier" focusable>
        <form method="POST" action="{{ route('suppliers.store') }}" class="p-6">
            @csrf
            <h2 class="text-lg font-medium text-slate-900">Tambah Supplier Baru</h2>

            <div class="mt-4">
                <x-input-label for="name" value="Company Name" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" required />
            </div>

            <div class="grid grid-cols-2 gap-4 mt-4">
                <div>
                    <x-input-label for="contact_person" value="Contact Person" />
                    <x-text-input id="contact_person" name="contact_person" type="text" class="mt-1 block w-full" />
                </div>
                <div>
                    <x-input-label for="phone" value="Phone" />
                    <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" />
                </div>
            </div>

            <div class="mt-4 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">Cancel</x-secondary-button>
                <x-primary-button class="ms-3">Save</x-primary-button>
            </div>
        </form>
    </x-modal>
</x-app-layout>
