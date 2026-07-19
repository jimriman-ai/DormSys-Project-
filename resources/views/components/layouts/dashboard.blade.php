<!DOCTYPE html>
<html lang="fa" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'DormSys') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased">
        <div class="flex min-h-screen flex-col" data-testid="dashboard-shell">
            <header class="sticky top-0 z-10 border-b border-slate-200 bg-white">
                <div class="flex items-center px-4 py-4 md:px-6">
                    <span class="text-lg font-semibold text-slate-900">
                        {{ config('app.name', 'DormSys') }}
                    </span>
                </div>
            </header>

            <div class="flex flex-1 flex-col md:flex-row">
                <aside
                    class="w-full shrink-0 border-b border-slate-200 bg-white md:w-56 md:border-b-0 md:border-e"
                    data-testid="dashboard-sidebar"
                >
                    <nav class="flex flex-col gap-1 p-3 text-sm" aria-label="dashboard" data-testid="dashboard-nav">
                        @foreach ($dashboard_nav_items ?? [] as $item)
                            <a
                                href="{{ $item['url'] }}"
                                data-testid="dashboard-nav-{{ $item['key'] }}"
                                class="rounded-lg px-3 py-2 {{ ! empty($item['active']) ? 'bg-sky-50 font-semibold text-sky-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                            >
                                {{ $item['label'] }}
                            </a>
                        @endforeach
                    </nav>
                    {{ $sidebar ?? '' }}
                </aside>

                <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-8 md:px-6">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @livewireScripts
    </body>
</html>
