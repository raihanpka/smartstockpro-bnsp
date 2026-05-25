<x-app-layout>
    <x-slot name="header">
        Request Stock Transfer
    </x-slot>

    <div class="mb-4">
        <h3 class="text-xl font-medium text-slate-900">New Transfer Request</h3>
        <p class="text-base text-slate-500">Ajukan perpindahan barang antar gudang.</p>
    </div>

    @if(session('error'))
        <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200">
            {{ session('error') }}
        </div>
    @endif

    <x-card class="max-w-2xl p-6">
        <form method="POST" action="{{ route('transfers.store') }}" class="space-y-6">
            @csrf

            <div>
                <x-input-label for="product_id" value="Product" class="text-base" />
                <select id="product_id" name="product_id" class="mt-1 block w-full text-base h-10 rounded-md border border-slate-200 bg-transparent px-3 py-1 shadow-sm focus:ring-1 focus:ring-slate-950" required>
                    <option value="">-- Select Product --</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }} (Stok: {{ $product->stock }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="source_warehouse_id" value="From Warehouse" class="text-base" />
                    <select id="source_warehouse_id" name="source_warehouse_id" class="mt-1 block w-full text-base h-10 rounded-md border border-slate-200 bg-transparent px-3 py-1 shadow-sm focus:ring-1 focus:ring-slate-950" required>
                        <option value="">-- Select Source --</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="destination_warehouse_id" value="To Warehouse" class="text-base" />
                    <select id="destination_warehouse_id" name="destination_warehouse_id" class="mt-1 block w-full text-base h-10 rounded-md border border-slate-200 bg-transparent px-3 py-1 shadow-sm focus:ring-1 focus:ring-slate-950" required>
                        <option value="">-- Select Destination --</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <x-input-label for="quantity" value="Quantity to Transfer" class="text-base" />
                <x-text-input id="quantity" name="quantity" type="number" min="1" class="mt-1 block w-full text-base h-10" required />
            </div>

            <div class="flex items-center gap-4">
                <x-primary-button class="h-10 text-base">Submit Request</x-primary-button>
                <a href="{{ route('transfers.index') }}" class="text-base font-medium text-slate-500 hover:text-slate-900">Cancel</a>
            </div>
        </form>
    </x-card>
</x-app-layout>
