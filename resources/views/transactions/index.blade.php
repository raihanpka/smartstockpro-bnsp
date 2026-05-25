<x-app-layout>
    <x-slot name="header">
        Stock Transactions
    </x-slot>

    <div class="mb-4 flex justify-between items-center">
        <div>
            <h3 class="text-lg font-medium text-slate-900">Recent Activity</h3>
            <p class="text-sm text-slate-500">Log transaksi masuk dan keluar.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('transactions.create') }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-slate-950 disabled:pointer-events-none disabled:opacity-50 bg-slate-900 text-slate-50 shadow hover:bg-slate-900/90 h-9 px-4 py-2">
                New Transaction
            </a>
        </div>
    </div>

    <x-card class="p-0 overflow-hidden">
        <x-table>
            <x-slot name="header">
                <th class="h-10 px-4 text-left font-medium">Date</th>
                <th class="h-10 px-4 text-left font-medium">Type</th>
                <th class="h-10 px-4 text-left font-medium">Product</th>
                <th class="h-10 px-4 text-left font-medium">Quantity</th>
                <th class="h-10 px-4 text-left font-medium">Warehouse</th>
            </x-slot>
            
            @forelse($transactions as $trx)
            <tr class="border-b transition-colors hover:bg-slate-50/50">
                <td class="p-4 text-sm text-slate-500">{{ $trx->created_at->format('M d, Y H:i') }}</td>
                <td class="p-4">
                    @if($trx->type === 'in')
                        <x-badge variant="default" class="bg-emerald-500 hover:bg-emerald-600">INBOUND</x-badge>
                    @else
                        <x-badge variant="outline" class="text-orange-500 border-orange-200">OUTBOUND</x-badge>
                    @endif
                </td>
                <td class="p-4 font-medium">{{ $trx->product->name ?? 'Unknown' }}</td>
                <td class="p-4">{{ $trx->quantity }}</td>
                <td class="p-4 text-slate-500">{{ $trx->warehouse->name ?? 'Unknown' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="p-4 text-center text-slate-500">No transactions recorded.</td>
            </tr>
            @endforelse
        </x-table>
    </x-card>
</x-app-layout>
