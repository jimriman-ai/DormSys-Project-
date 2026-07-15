<!DOCTYPE html>
<html lang="fa" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'DormSys') }} — مدیریت خوابگاه</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased">
        <div class="min-h-screen">
            <header class="border-b border-slate-200 bg-white">
                <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-4">
                    <div class="flex items-center gap-6">
                        <span class="text-lg font-semibold text-slate-900">مدیریت خوابگاه</span>
                        <nav class="flex items-center gap-4 text-sm text-slate-600" aria-label="dormitory-admin">
                            <a href="{{ route('dormitory-admin.manager') }}" class="hover:text-slate-900">داشبورد مدیر</a>
                            <a href="{{ route('dormitory-admin.unit-manager') }}" class="hover:text-slate-900">داشبورد واحد</a>
                        </nav>
                    </div>
                </div>
            </header>

            <main class="mx-auto max-w-6xl px-4 py-8">
                {{ $slot }}
            </main>
        </div>

        @livewireScripts
    </body>
</html>
