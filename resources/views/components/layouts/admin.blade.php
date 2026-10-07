@props([
    'title',
    'subtitle',
])

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $title }} — RequestPilot</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="min-h-dvh overflow-x-hidden bg-slate-300 text-slate-950 antialiased">
        @php
            $navigation = [
                ['label' => 'Overview', 'route' => 'admin.overview', 'active' => 'admin.overview', 'icon' => 'overview'],
                ['label' => 'Inquiries', 'route' => 'admin.inquiries', 'active' => 'admin.inquiries*', 'icon' => 'inquiries'],
                ['label' => 'Human Review', 'route' => 'admin.review', 'active' => 'admin.review', 'icon' => 'review'],
            ];
        @endphp

        <a href="#admin-content" class="sr-only fixed left-4 top-4 z-50 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white focus:not-sr-only focus:outline-none focus:ring-4 focus:ring-indigo-300">
            Skip to main content
        </a>

        <div class="min-h-dvh overflow-x-hidden lg:h-dvh lg:overflow-hidden lg:pl-[17rem]">
            <aside class="fixed inset-y-0 left-0 z-40 hidden h-dvh w-[17rem] flex-col overflow-hidden border-r border-slate-700 bg-slate-800 shadow-xl shadow-slate-950/10 lg:flex">
                <div class="flex h-20 shrink-0 items-center border-b border-slate-700 px-6">
                    <a href="{{ route('admin.overview') }}" class="flex items-center gap-3 rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-300 focus-visible:ring-offset-4 focus-visible:ring-offset-slate-800" aria-label="RequestPilot admin overview">
                        <span class="flex size-9 items-center justify-center rounded-xl bg-indigo-500 text-white shadow-md shadow-indigo-950/30 ring-1 ring-indigo-400/50">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 5v14M5 5h6a4 4 0 0 1 0 8H5m6 0 7 6" />
                            </svg>
                        </span>
                        <span>
                            <span class="block text-base font-bold tracking-tight text-white">RequestPilot</span>
                            <span class="block text-xs font-medium text-slate-300">Operations console</span>
                        </span>
                    </a>
                </div>

                <nav class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-4 py-6" aria-label="Primary admin navigation">
                    <p class="mb-3 px-3 text-[0.68rem] font-bold uppercase tracking-[0.18em] text-slate-500">Workspace</p>
                    <div class="space-y-1">
                    @foreach ($navigation as $item)
                        <a
                            href="{{ route($item['route']) }}"
                            @class([
                                'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-300 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-800',
                                'bg-indigo-500 text-white shadow-md shadow-indigo-950/30 ring-1 ring-indigo-400/70' => request()->routeIs($item['active']),
                                'text-slate-300 hover:bg-slate-700 hover:text-white' => ! request()->routeIs($item['active']),
                            ])
                            @if (request()->routeIs($item['active'])) aria-current="page" @endif
                        >
                            @if ($item['icon'] === 'overview')
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4h6v6H4V4Zm10 0h6v6h-6V4ZM4 14h6v6H4v-6Zm10 0h6v6h-6v-6Z" />
                                </svg>
                            @elseif ($item['icon'] === 'inquiries')
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h7m-9 9 3.4-3H19a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2v3Z" />
                                </svg>
                            @else
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3 4.5 6v5.25c0 4.63 3.2 8.95 7.5 9.75 4.3-.8 7.5-5.12 7.5-9.75V6L12 3Zm-3 9 2 2 4-4" />
                                </svg>
                            @endif
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                    </div>
                </nav>

                <div class="shrink-0 border-t border-slate-700 bg-slate-800/95 p-4">
                    <div class="flex items-center gap-3 rounded-xl border border-slate-700 bg-slate-900/30 px-3 py-2.5">
                        <span class="flex size-9 items-center justify-center rounded-full bg-indigo-500/20 text-sm font-semibold text-indigo-200 ring-1 ring-indigo-400/30">AD</span>
                        <span class="min-w-0">
                            <span class="block truncate text-sm font-semibold text-slate-100">Admin</span>
                            <span class="block truncate text-xs text-slate-400">Workspace owner</span>
                        </span>
                    </div>
                </div>
            </aside>

            <div class="min-w-0 lg:h-dvh lg:overflow-y-auto lg:overscroll-contain">
                <header class="sticky top-0 z-30 border-b border-slate-400/80 bg-slate-200/95 shadow-sm shadow-slate-900/10 backdrop-blur supports-[backdrop-filter]:bg-slate-200/90">
                    <div class="flex h-14 items-center justify-between gap-4 bg-slate-800 px-4 sm:px-6 lg:hidden">
                        <a href="{{ route('admin.overview') }}" class="flex items-center gap-2.5 rounded-lg font-bold tracking-tight text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-300 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-800">
                            <span class="flex size-8 items-center justify-center rounded-lg bg-indigo-500 text-white shadow-sm shadow-indigo-950/30 ring-1 ring-indigo-400/50">
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 5v14M5 5h6a4 4 0 0 1 0 8H5m6 0 7 6" />
                                </svg>
                            </span>
                            RequestPilot
                        </a>

                        <details class="relative">
                            <summary class="flex size-10 cursor-pointer list-none items-center justify-center rounded-lg border border-slate-600 bg-slate-700 text-slate-100 transition hover:border-slate-500 hover:bg-slate-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-300 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-800" aria-label="Open admin navigation">
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" />
                                </svg>
                            </summary>
                            <nav class="absolute right-0 z-30 mt-2 w-[min(16rem,calc(100vw-2rem))] space-y-1 rounded-xl border border-slate-600 bg-slate-800 p-2 shadow-xl shadow-slate-950/30" aria-label="Mobile admin navigation">
                                @foreach ($navigation as $item)
                                    <a
                                        href="{{ route($item['route']) }}"
                                        @class([
                                            'block rounded-lg px-3 py-2.5 text-sm font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-300',
                                            'bg-indigo-500 text-white shadow-sm ring-1 ring-indigo-400/60' => request()->routeIs($item['active']),
                                            'text-slate-300 hover:bg-slate-700 hover:text-white' => ! request()->routeIs($item['active']),
                                        ])
                                        @if (request()->routeIs($item['active'])) aria-current="page" @endif
                                    >
                                        {{ $item['label'] }}
                                    </a>
                                @endforeach
                            </nav>
                        </details>
                    </div>

                    <div class="flex items-center justify-between gap-6 px-4 py-3.5 sm:px-6 sm:py-4 lg:min-h-20 lg:px-8 lg:py-4">
                        <div class="min-w-0">
                            <h1 class="text-xl font-bold tracking-tight text-slate-950 sm:text-2xl">{{ $title }}</h1>
                            <p class="mt-1 text-sm text-slate-600">{{ $subtitle }}</p>
                        </div>
                        <div class="hidden shrink-0 items-center gap-3 sm:flex">
                            <div class="text-right">
                                <p class="text-sm font-semibold text-slate-900">Admin</p>
                                <p class="text-xs text-slate-600">RequestPilot workspace</p>
                            </div>
                            <span class="flex size-9 items-center justify-center rounded-full border border-slate-400 bg-slate-300 text-xs font-bold text-slate-800 shadow-sm">AD</span>
                        </div>
                    </div>
                </header>

                <main id="admin-content" class="min-w-0 px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
                    <div class="mx-auto max-w-7xl">
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>

        @livewireScripts
    </body>
</html>
