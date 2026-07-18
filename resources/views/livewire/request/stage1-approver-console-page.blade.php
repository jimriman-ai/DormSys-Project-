{{-- [PERMIT-ID: IMPL-PERMIT-03] Stage-1 Approver Console — approve/reject --}}
<div class="mx-auto max-w-4xl px-4 py-8" dir="rtl">
    <h1 class="text-xl font-semibold text-slate-900">کنسول تأیید مرحله یک (مدیر واحد)</h1>
    <p class="mt-2 text-sm text-slate-600">
        تأیید یا رد درخواست‌های در انتظار مدیر واحد.
    </p>

    @if ($requestId)
        <p class="mt-4 text-sm text-slate-700">شناسه درخواست: <span class="font-mono">{{ $requestId }}</span></p>

        <div class="mt-6 flex flex-wrap gap-3">
            <button
                type="button"
                wire:click="approve"
                wire:loading.attr="disabled"
                class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-800"
            >
                تأیید مرحله یک
            </button>
        </div>

        <div class="mt-6 space-y-2">
            <label for="rejectionReason" class="block text-sm font-medium text-slate-700">دلیل رد</label>
            <textarea
                id="rejectionReason"
                wire:model="rejectionReason"
                rows="3"
                class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
            ></textarea>
            @error('rejectionReason')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
            <button
                type="button"
                wire:click="reject"
                wire:loading.attr="disabled"
                class="rounded-md bg-red-700 px-4 py-2 text-sm font-medium text-white hover:bg-red-800"
            >
                رد درخواست
            </button>
        </div>
    @else
        <p class="mt-4 text-sm text-slate-600">برای اقدام، شناسه درخواست را از مسیر کنسول باز کنید.</p>
    @endif
</div>
