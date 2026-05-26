<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SmartStock Pro') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-white text-slate-900">
        <div class="min-h-screen flex">
            <!-- Sidebar -->
            <aside class="w-64 border-r border-slate-200 bg-slate-50/50 hidden md:block">
                <div class="h-full px-3 py-4 overflow-y-auto">
                    <div class="mb-6 px-2">
                        <h2 class="text-lg font-semibold tracking-tight text-slate-900">
                            SmartStock Pro
                        </h2>
                    </div>
                    <ul class="space-y-2 font-medium text-sm">
                        <li>
                            <a href="/dashboard" class="flex items-center p-2 rounded-md text-slate-900 hover:bg-slate-100 transition-colors">
                                <svg class="w-5 h-5 text-slate-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 22 21">
                                    <path d="M16.975 11H10V4.025a1 1 0 0 0-1.066-.998 8.5 8.5 0 1 0 9.039 9.039.999.999 0 0 0-1-1.066h.002Z"/>
                                    <path d="M12.5 0c-.157 0-.311.01-.565.027A1 1 0 0 0 11 1.02V10h8.975a1 1 0 0 0 1-.935c.013-.188.028-.374.028-.565A8.51 8.51 0 0 0 12.5 0Z"/>
                                </svg>
                                <span class="ms-3">Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="/products" class="flex items-center p-2 rounded-md text-slate-900 hover:bg-slate-100 transition-colors">
                                <svg class="w-5 h-5 text-slate-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 18 20">
                                    <path d="M17 5.923A1 1 0 0 0 16 5h-3V4a4 4 0 1 0-8 0v1H2a1 1 0 0 0-1 .923L.086 17.846A2 2 0 0 0 2.08 20h13.84a2 2 0 0 0 1.994-2.153L17 5.923ZM7 9a1 1 0 0 1-2 0V7h2v2Zm0-5a2 2 0 1 1 4 0v1H7V4Zm6 5a1 1 0 1 1-2 0V7h2v2Z"/>
                                </svg>
                                <span class="ms-3">Inventaris Produk</span>
                            </a>
                        </li>
                        <li>
                            <a href="/transactions" class="flex items-center p-2 rounded-md text-slate-900 hover:bg-slate-100 transition-colors">
                                <svg class="w-5 h-5 text-slate-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 16">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 8h11m0 0L8 4m4 4-4 4m4-11h3a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-3"/>
                                </svg>
                                <span class="ms-3">Transaksi</span>
                            </a>
                        </li>
                        <li>
                            <a href="/transfers" class="flex items-center p-2 rounded-md text-slate-900 hover:bg-slate-100 transition-colors">
                                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                                <span class="ms-3">Transfer Gudang</span>
                            </a>
                        </li>
                        <li class="pt-4 pb-2">
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider px-2">Kelola Katalog</span>
                        </li>
                        <li>
                            <a href="/catalog" class="flex items-center p-2 rounded-md text-slate-900 hover:bg-slate-100 transition-colors">
                                <svg class="w-5 h-5 text-slate-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                                <span class="ms-3">Katalog Produk</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto">
                <!-- Header -->
                <header class="border-b border-slate-200 bg-white px-6 py-3 flex items-center justify-between">
                    <h2 class="font-semibold text-lg text-slate-900">
                        {{ $header ?? 'Overview' }}
                    </h2>
                    
                    <div class="flex items-center gap-4">
                        <span class="text-sm font-medium text-slate-700">{{ Auth::user()->name ?? 'User' }}</span>
                        <!-- Logout form -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-secondary-button type="submit" class="h-8 px-3 text-xs">
                                Log Out
                            </x-secondary-button>
                        </form>
                    </div>
                </header>

                <div class="p-6">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </body>
</html>
