{{-- [PERMIT-ID: IMPL-PERMIT-03] Stage-1 Approver Console — approve/reject + F-W07-04 list/filter --}}
<div class="mx-auto max-w-4xl px-4 py-8" dir="rtl">
    <h1 class="text-xl font-semibold text-slate-900">کنسول تأیید مرحله یک (مدیر واحد)</h1>
    <p class="mt-2 text-sm text-slate-600">
        تأیید یا رد درخواست‌های در انتظار مدیر واحد.
    </p>

    @if (! $requestId)
        <div class="mt-6">
            <label for="stage1-search" class="block text-sm font-medium text-slate-700">جستجو</label>
            <input
                id="stage1-search"
                type="search"
                wire:model.live="search"
                placeholder="شناسه درخواست، کد، یا شناسه کارمند"
                class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
            />
        </div>

        <ul class="mt-6 space-y-3" data-testid="stage1-pending-list">
            @forelse ($pendingRequests as $req)
                <li
                    class="rounded-lg border border-slate-200 bg-white p-4"
                    data-testid="stage1-pending-row"
                    data-request-id="{{ $req['id'] }}"
                >
                    <a
                        href="{{ route('approvals.stage1.show', ['requestId' => $req['id']]) }}"
                        class="block text-sm text-slate-800 hover:text-sky-700"
                    >
                        <span class="font-mono text-xs text-slate-500">{{ $req['id'] }}</span>
                        <span class="mt-1 block font-medium">{{ $req['code'] }}</span>
                        <span class="mt-1 block text-xs text-slate-600">
                            درخواست‌کننده (employee_id):
                            <span class="font-mono">{{ $req['employee_id'] }}</span>
                        </span>
                        @if ($req['submitted_at'])
                            <span class="mt-1 block text-xs text-slate-500">{{ $req['submitted_at'] }}</span>
                        @endif
                    </a>
                </li>
            @empty
                <li class="text-sm text-slate-600" data-testid="stage1-pending-empty">
                    هیچ درخواست در انتظاری وجود ندارد
                </li>
            @endforelse
        </ul>
    @endif

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
    @endif
</div>
