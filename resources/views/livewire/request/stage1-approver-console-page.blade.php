{{-- [PERMIT-ID: IMPL-PERMIT-03] Stage-1 Approver Console — approve/reject + F-W07-04 Wave 2 list/filter --}}
<div class="mx-auto max-w-4xl px-4 py-8" dir="rtl">
    <h1 class="text-xl font-semibold text-slate-900">کنسول تأیید مرحله یک</h1>
    <p class="mt-2 text-sm text-slate-600">
        تأیید یا رد درخواست‌های صف مرحله یک.
        <span class="block mt-1 text-xs text-slate-500" data-testid="stage1-stage-label">
            {{-- Display mapping only (SB-D2): business stage pending_department_manager → Persian label; auth role remains dormitory-manager. --}}
            وضعیت صف: در انتظار مدیر واحد · نقش دسترسی: مدیر خوابگاه
        </span>
    </p>

    @if (! $requestId)
        <div class="mt-6 space-y-3">
            <div class="flex flex-wrap items-end gap-3">
                <div class="min-w-[16rem] flex-1">
                    <label for="stage1-search" class="block text-sm font-medium text-slate-700">جستجو</label>
                    <input
                        id="stage1-search"
                        type="search"
                        wire:model.live.debounce.300ms="search"
                        placeholder="شناسه درخواست، کد، یا شناسه کارمند"
                        class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                        data-testid="stage1-search-input"
                    />
                </div>
                @if (trim($search) !== '')
                    <button
                        type="button"
                        wire:click="clearSearch"
                        class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 hover:bg-slate-50"
                        data-testid="stage1-search-clear"
                    >
                        پاک کردن
                    </button>
                @endif
            </div>

            <div
                wire:loading
                wire:target="search,clearSearch,loadRequests,goToPage,page"
                class="text-xs text-slate-500"
                data-testid="stage1-pending-loading"
            >
                در حال به‌روزرسانی فهرست…
            </div>

            @if ($listTotal > 0)
                <p class="text-xs text-slate-500" data-testid="stage1-pending-count">
                    {{ $listTotal }} درخواست · صفحه {{ $listPage }} از {{ $listLastPage }}
                </p>
            @endif
        </div>

        <ul class="mt-6 space-y-3" data-testid="stage1-pending-list">
            @forelse ($visibleRequests as $req)
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
                @if ($isFilterEmpty)
                    <li class="text-sm text-slate-600" data-testid="stage1-pending-filter-empty">
                        نتیجه‌ای برای این جستجو یافت نشد.
                    </li>
                @else
                    <li class="text-sm text-slate-600" data-testid="stage1-pending-empty">
                        هیچ درخواست در انتظاری وجود ندارد
                    </li>
                @endif
            @endforelse
        </ul>

        @if ($listLastPage > 1)
            <nav class="mt-4 flex items-center justify-center gap-3" aria-label="صفحه‌بندی صف مرحله یک" data-testid="stage1-pending-pagination">
                <button
                    type="button"
                    wire:click="goToPage({{ max(1, $listPage - 1) }})"
                    @disabled($listPage <= 1)
                    class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-700 disabled:opacity-40"
                    data-testid="stage1-page-prev"
                >
                    قبلی
                </button>
                <button
                    type="button"
                    wire:click="goToPage({{ min($listLastPage, $listPage + 1) }})"
                    @disabled($listPage >= $listLastPage)
                    class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-700 disabled:opacity-40"
                    data-testid="stage1-page-next"
                >
                    بعدی
                </button>
            </nav>
        @endif
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
