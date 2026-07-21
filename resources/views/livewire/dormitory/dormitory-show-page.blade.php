<div data-testid="dormitory-show-page" data-dormitory-id="{{ $dormitory->id }}">
    <x-ui.page-header
        title="{{ $dormitory->name }}"
        description="مشاهده اطلاعات پایه خوابگاه (فقط خواندنی)."
    />

    <dl class="mt-6 max-w-xl space-y-4 rounded-xl border border-slate-200 bg-white p-6 text-sm">
        <div>
            <dt class="text-slate-500">کد</dt>
            <dd class="mt-1 font-medium text-slate-900" dir="ltr" data-testid="dormitory-show-code">
                {{ $dormitory->code }}
            </dd>
        </div>
        <div>
            <dt class="text-slate-500">نام</dt>
            <dd class="mt-1 font-medium text-slate-900" data-testid="dormitory-show-name">
                {{ $dormitory->name }}
            </dd>
        </div>
        <div>
            <dt class="text-slate-500">وضعیت</dt>
            <dd class="mt-1 font-medium text-slate-900" dir="ltr" data-testid="dormitory-show-status">
                {{ $dormitory->status }}
            </dd>
        </div>
    </dl>

    <div class="mt-6">
        <a
            href="{{ route('dormitories.index') }}"
            class="text-sm font-medium text-sky-700 hover:text-sky-800"
            data-testid="dormitory-show-back"
        >
            بازگشت به فهرست خوابگاه‌ها
        </a>
    </div>
</div>
