<!DOCTYPE html>
<html lang="fa" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>ورود | {{ config('app.name', 'DormSys') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-100 font-sans text-slate-900 antialiased">
        <main class="mx-auto flex min-h-screen max-w-md items-center px-4 py-12">
            <div class="w-full rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h1 class="mb-6 text-center text-xl font-semibold">ورود به سامانه</h1>

                @if (session('error'))
                    <x-ui.alert type="error" :message="session('error')" class="mb-4" />
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    <x-ui.form-field label="ایمیل" for="identifier" :error="$errors->first('identifier')">
                        <input
                            id="identifier"
                            name="identifier"
                            type="email"
                            value="{{ old('identifier') }}"
                            required
                            autocomplete="username"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200"
                        >
                    </x-ui.form-field>

                    <x-ui.form-field label="رمز عبور" for="password" :error="$errors->first('password')">
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            autocomplete="current-password"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200"
                        >
                    </x-ui.form-field>

                    <x-ui.button type="submit" class="w-full">ورود</x-ui.button>
                </form>
            </div>
        </main>
    </body>
</html>
