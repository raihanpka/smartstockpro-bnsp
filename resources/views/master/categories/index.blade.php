<x-app-layout>
    <x-slot name="header">
        Categories Management
    </x-slot>

    <div class="mb-4 flex justify-between items-center">
        <div>
            <h3 class="text-lg font-medium text-slate-900">Master Data: Categories</h3>
            <p class="text-sm text-slate-500">Kelola daftar kategori produk.</p>
        </div>
        <x-primary-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'create-category')">Add Category</x-primary-button>
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
                <th class="h-10 px-4 text-left font-medium">Description</th>
            </x-slot>
            
            @forelse($categories as $category)
            <tr class="border-b transition-colors hover:bg-slate-50/50">
                <td class="p-4 font-medium">{{ $category->name }}</td>
                <td class="p-4 text-slate-500">{{ $category->description ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="2" class="p-4 text-center text-slate-500">No categories found.</td>
            </tr>
            @endforelse
        </x-table>
    </x-card>

    <x-modal name="create-category" focusable>
        <form method="POST" action="{{ route('categories.store') }}" class="p-6">
            @csrf
            <h2 class="text-lg font-medium text-slate-900">
                Tambah Kategori Baru
            </h2>

            <div class="mt-6">
                <x-input-label for="name" value="Category Name" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" placeholder="Elektronik" required autofocus />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Cancel
                </x-secondary-button>
                <x-primary-button class="ms-3">
                    Save Category
                </x-primary-button>
            </div>
        </form>
    </x-modal>
</x-app-layout>
