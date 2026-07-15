<main class="mx-auto flex min-h-screen max-w-md items-center px-4 py-12">
    <div class="w-full rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="mb-6 text-center text-xl font-semibold">ورود کارکنان</h1>

        @if ($errorMessage)
            <x-ui.alert type="error" :message="$errorMessage" class="mb-4" />
        @endif

        <form wire:submit="login" class="space-y-4">
            <x-ui.form-field label="ایمیل" for="identifier" :error="$errors->first('identifier')">
                <input
                    id="identifier"
                    wire:model="identifier"
                    type="email"
                    required
                    autocomplete="username"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200"
                >
            </x-ui.form-field>

            <x-ui.form-field label="رمز عبور" for="password" :error="$errors->first('password')">
                <input
                    id="password"
                    wire:model="password"
                    type="password"
                    required
                    autocomplete="current-password"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200"
                >
            </x-ui.form-field>

            <x-ui.button type="submit" class="w-full" wire:loading.attr="disabled">
                ورود
            </x-ui.button>
        </form>
    </div>
</main>
