<div>
    <x-ui.page-header title="ثبت درخواست شخصی" description="اطلاعات درخواست را وارد کنید. اعتبارسنجی نهایی در سامانه انجام می‌شود." />

    @if ($actionError)
        <x-ui.alert type="error" :message="$actionError" class="mb-4" />
    @endif

    <form wire:submit="save" class="max-w-xl space-y-4 rounded-xl border border-slate-200 bg-white p-6">
        <x-ui.form-field label="شناسه خوابگاه" for="dormitoryId" :error="$errors->first('dormitoryId')">
            <input
                id="dormitoryId"
                type="text"
                wire:model="dormitoryId"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200"
                dir="ltr"
            >
        </x-ui.form-field>

        <x-ui.form-field label="تاریخ ورود" for="checkInDate" :error="$errors->first('checkInDate')">
            <input
                id="checkInDate"
                type="date"
                wire:model="checkInDate"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200"
            >
        </x-ui.form-field>

        <x-ui.form-field label="تاریخ خروج" for="checkOutDate" :error="$errors->first('checkOutDate')">
            <input
                id="checkOutDate"
                type="date"
                wire:model="checkOutDate"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200"
            >
        </x-ui.form-field>

        <div class="flex items-center gap-3 pt-2">
            <x-ui.button type="submit" wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save">ثبت درخواست</span>
                <span wire:loading wire:target="save">در حال ثبت...</span>
            </x-ui.button>

            <a href="{{ route('requests.index') }}" class="text-sm text-slate-600 hover:text-slate-900" wire:navigate>
                بازگشت
            </a>
        </div>
    </form>
</div>
