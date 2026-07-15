<!DOCTYPE html>
<html lang="fa" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'ورود' }} | {{ config('app.name', 'DormSys') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="min-h-screen bg-slate-100 font-sans text-slate-900 antialiased">
        {{ $slot }}
        @livewireScripts
    </body>
</html>
