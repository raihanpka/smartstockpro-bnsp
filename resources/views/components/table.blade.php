<div class="relative w-full overflow-auto rounded-md border border-slate-200">
    <table class="w-full caption-bottom text-sm text-left">
        @if(isset($header))
            <thead class="[&_tr]:border-b bg-slate-50/50">
                <tr class="border-b transition-colors hover:bg-slate-100/50 data-[state=selected]:bg-slate-100 text-slate-500 font-medium">
                    {{ $header }}
                </tr>
            </thead>
        @endif
        <tbody class="[&_tr:last-child]:border-0 text-slate-900">
            {{ $slot }}
        </tbody>
    </table>
</div>
