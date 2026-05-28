<x-app-layout>
    <x-slot name="header">
        Kelola Kategori Produk
    </x-slot>

    <div class="mb-4 flex justify-between items-center">
        <div>
            <h3 class="text-lg font-medium text-slate-900">Kelola Kategori Produk</h3>
            <p class="text-sm text-slate-500">Kelola daftar kategori untuk klasifikasi produk Anda.</p>
        </div>
        <x-primary-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'create-category')">Tambah Kategori</x-primary-button>
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
                <th class="h-10 px-4 text-left font-medium">Nama Kategori</th>
                <th class="h-10 px-4 text-left font-medium">Deskripsi</th>
                <th class="h-10 px-4 text-right font-medium">Total Produk</th>
                <th class="h-10 px-4 text-right font-medium">Aksi</th>
            </x-slot>
            @forelse($categories as $category)
            <tr class="border-b transition-colors hover:bg-slate-50/50">
                <td class="p-4 font-medium">{{ $category->name }}</td>
                <td class="p-4 text-slate-500">{{ $category->description ?? '-' }}</td>
                <td class="p-4 text-right">{{ $category->products_count }}</td>
                <td class="p-4 text-right">
                    <button
                        x-data
                        @click="$dispatch('open-modal', 'edit-category-{{ $category->id }}')"
                        class="text-slate-500 hover:text-slate-900 font-medium text-sm mr-2">Edit</button>
                    <form method="POST" action="{{ route('categories.destroy', $category) }}" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-700 font-medium text-sm"
                            onclick="return confirm('Hapus kategori ini?')">Hapus</button>
                    </form>
                </td>
            </tr>
            {{-- Edit Modal per category --}}
            <x-modal name="edit-category-{{ $category->id }}" focusable>
                <form method="POST" action="{{ route('categories.update', $category) }}" class="p-6">
                    @csrf @method('PUT')
                    <h2 class="text-lg font-medium text-slate-900">Edit Kategori</h2>
                    <div class="mt-4">
                        <x-input-label for="edit_cat_name_{{ $category->id }}" value="Nama Kategori" />
                        <x-text-input id="edit_cat_name_{{ $category->id }}" name="name" type="text"
                            class="mt-1 block w-full" value="{{ $category->name }}" required />
                    </div>
                    <div class="mt-4">
                        <x-input-label for="edit_cat_desc_{{ $category->id }}" value="Deskripsi" />
                        <textarea id="edit_cat_desc_{{ $category->id }}" name="description" rows="3"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ $category->description }}</textarea>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <x-secondary-button x-on:click="$dispatch('close')">Batal</x-secondary-button>
                        <x-primary-button class="ms-3">Simpan</x-primary-button>
                    </div>
                </form>
            </x-modal>
            @empty
            <tr>
                <td colspan="4" class="p-4 text-center text-slate-500">Tidak ada kategori.</td>
            </tr>
            @endforelse
        </x-table>
    </x-card>

    <div class="mt-4">
        {{ $categories->links() }}
    </div>

    <!-- Create Modal -->
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
</x-app-layout>
