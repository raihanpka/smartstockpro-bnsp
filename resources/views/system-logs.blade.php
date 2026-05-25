<x-app-layout>
    <x-slot name="header">
        System Logs Monitor
    </x-slot>

    <div class="mb-4 flex justify-between items-center">
        <div>
            <h3 class="text-lg font-medium text-slate-900">Application Error Logs</h3>
            <p class="text-sm text-slate-500">Log aplikasi yang dikategorikan berdasarkan severity (INFO, WARNING, ERROR).</p>
        </div>
    </div>

    <x-card class="p-0 overflow-hidden">
        <x-table>
            <x-slot name="header">
                <th class="h-10 px-4 text-left font-medium">Timestamp</th>
                <th class="h-10 px-4 text-left font-medium">Severity</th>
                <th class="h-10 px-4 text-left font-medium">Environment</th>
                <th class="h-10 px-4 text-left font-medium">Message</th>
            </x-slot>
            
            @forelse($logs as $log)
            <tr class="border-b transition-colors hover:bg-slate-50/50">
                <td class="p-4 text-sm text-slate-500 whitespace-nowrap">{{ $log['timestamp'] }}</td>
                <td class="p-4">
                    @if($log['severity'] === 'ERROR')
                        <x-badge variant="destructive">Critical</x-badge>
                    @elseif($log['severity'] === 'WARNING')
                        <x-badge variant="outline" class="text-orange-500 border-orange-200">Warning</x-badge>
                    @else
                        <x-badge variant="default" class="bg-blue-500">Info</x-badge>
                    @endif
                </td>
                <td class="p-4 text-sm text-slate-500">{{ $log['env'] }}</td>
                <td class="p-4 text-sm font-mono truncate max-w-xl" title="{{ $log['message'] }}">{{ Str::limit($log['message'], 100) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="p-4 text-center text-slate-500">No recent logs found.</td>
            </tr>
            @endforelse
        </x-table>
    </x-card>
</x-app-layout>
