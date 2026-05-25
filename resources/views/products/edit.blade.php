<x-app-layout>
    <x-slot name="header">
        Edit Product
    </x-slot>

    <div class="mb-4 flex items-center justify-between">
        <div>
            <h3 class="text-xl font-medium text-slate-900">Edit Product: {{ $product->name }}</h3>
            <p class="text-base text-slate-500">Ubah rincian produk yang sudah ada.</p>
        </div>
        
        <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?');">
            @csrf
            @method('DELETE')
            <x-primary-button class="bg-red-600 hover:bg-red-700 focus:ring-red-500">Delete Product</x-primary-button>
        </form>
    </div>

    <x-card class="max-w-2xl p-6">
        <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="name" value="Product Name" class="text-base" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full text-base h-10" :value="old('name', $product->name)" required autofocus />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>
                <div>
                    <x-input-label for="sku" value="SKU" class="text-base" />
                    <x-text-input id="sku" name="sku" type="text" class="mt-1 block w-full text-base h-10" :value="old('sku', $product->sku)" required />
                    <x-input-error class="mt-2" :messages="$errors->get('sku')" />
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="category_id" value="Category" class="text-base" />
                    <select id="category_id" name="category_id" class="mt-1 block w-full text-base h-10 rounded-md border border-slate-200 bg-transparent px-3 py-1 shadow-sm focus:ring-1 focus:ring-slate-950" required>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="warehouse_id" value="Warehouse" class="text-base" />
                    <select id="warehouse_id" name="warehouse_id" class="mt-1 block w-full text-base h-10 rounded-md border border-slate-200 bg-transparent px-3 py-1 shadow-sm focus:ring-1 focus:ring-slate-950" required>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ old('warehouse_id', $product->warehouse_id) == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="min_stock" value="Minimum Stock Level" class="text-base" />
                    <x-text-input id="min_stock" name="min_stock" type="number" min="0" class="mt-1 block w-full text-base h-10" :value="old('min_stock', $product->min_stock)" required />
                </div>
                <div>
                    <x-input-label for="image" value="Update Image (Optional)" class="text-base" />
                    <input type="file" name="image" id="image" class="mt-1 block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-slate-50 file:text-slate-700 hover:file:bg-slate-100" accept="image/*">
                    @if($product->image_path)
                        <p class="text-xs text-slate-400 mt-1">Currently has image.</p>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-4">
                <x-primary-button class="h-10 text-base">Update Product</x-primary-button>
                <a href="{{ route('products.index') }}" class="text-base font-medium text-slate-500 hover:text-slate-900">Cancel</a>
            </div>
        </form>
    </x-card>
</x-app-layout>
