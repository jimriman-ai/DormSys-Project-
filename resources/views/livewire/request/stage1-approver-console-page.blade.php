{{-- [PERMIT-ID: IMPL-PERMIT-01] Stage-1 Approver Console scaffold — approve/reject UI TBD --}}
<div class="mx-auto max-w-4xl px-4 py-8" dir="rtl">
    <h1 class="text-xl font-semibold text-slate-900">کنسول تأیید مرحله یک (مدیر واحد)</h1>
    <p class="mt-2 text-sm text-slate-600">
        سطح جداگانه تأیید — اقدامات تأیید/رد در مراحل بعدی این مجوز پیاده‌سازی می‌شود.
    </p>
    @if ($requestId)
        <p class="mt-4 text-sm text-slate-700">شناسه درخواست: <span class="font-mono">{{ $requestId }}</span></p>
    @endif
</div>
