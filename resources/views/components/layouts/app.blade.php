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
        <div class="min-h-screen">
            <header class="border-b border-slate-200 bg-white">
                <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-4">
                    <div class="flex items-center gap-6">
                        <a href="{{ route('requests.index') }}" class="text-lg font-semibold text-slate-900">
                            {{ config('app.name', 'DormSys') }}
                        </a>
                        <nav class="flex items-center gap-4 text-sm">
                            <a
                                href="{{ route('requests.index') }}"
                                class="{{ request()->routeIs('requests.*') ? 'font-semibold text-sky-700' : 'text-slate-600 hover:text-slate-900' }}"
                            >
                                درخواست‌ها
                            </a>
                            <a
                                href="{{ route('notifications.index') }}"
                                class="{{ request()->routeIs('notifications.*') ? 'font-semibold text-sky-700' : 'text-slate-600 hover:text-slate-900' }}"
                            >
                                اعلان‌ها
                                @if ($show_badge)
                                    <span class="ms-1 inline-flex min-w-[1.25rem] items-center justify-center rounded-full bg-sky-600 px-1.5 py-0.5 text-xs font-semibold text-white">{{ $unread_count }}</span>
                                @endif
                            </a>
                            <a
                                href="{{ route('employees.hub') }}"
                                class="{{ request()->routeIs('employees.*') ? 'font-semibold text-sky-700' : 'text-slate-600 hover:text-slate-900' }}"
                            >
                                کارکنان
                            </a>
                            @if ($show_audit_nav ?? false)
                                <a
                                    href="{{ route('audit.index') }}"
                                    class="{{ request()->routeIs('audit.*') ? 'font-semibold text-sky-700' : 'text-slate-600 hover:text-slate-900' }}"
                                >
                                    تاریخچه حسابرسی
                                </a>
                            @endif
                        </nav>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-ui.button type="submit" variant="secondary">خروج</x-ui.button>
                    </form>
                </div>
            </header>

            <main class="mx-auto max-w-6xl px-4 py-8">
                @if (session('success'))
                    <x-ui.alert type="success" :message="session('success')" class="mb-6" />
                @endif

                @if (session('error'))
                    <x-ui.alert type="error" :message="session('error')" class="mb-6" />
                @endif

                {{ $slot }}
            </main>
        </div>

        @livewireScripts
    </body>
</html>
