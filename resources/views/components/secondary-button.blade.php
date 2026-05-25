<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-slate-950 disabled:pointer-events-none disabled:opacity-50 border border-slate-200 bg-white text-slate-900 shadow-sm hover:bg-slate-100 hover:text-slate-900 h-9 px-4 py-2']) }}>
    {{ $slot }}
</button>
