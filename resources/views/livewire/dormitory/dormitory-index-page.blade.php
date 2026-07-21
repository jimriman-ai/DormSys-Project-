<div data-testid="dormitory-index-page">
    <x-ui.page-header
        title="خوابگاه‌ها"
        description="خوابگاه‌هایی که به شما اختصاص داده شده‌اند."
    />

    @if (count($dormitories) === 0)
        <x-ui.empty-state
            data-testid="dormitory-index-empty"
            title="خوابگاهی اختصاص داده نشده"
            description="در حال حاضر هیچ خوابگاه فعالی به حساب شما منتسب نیست. در صورت نیاز با واحد خوابگاه تماس بگیرید."
        />
    @else
        <ul class="mt-4 space-y-3" data-testid="dormitory-index-list">
            @foreach ($dormitories as $dormitory)
                <li
                    class="rounded-xl border border-slate-200 bg-white p-4"
                    data-testid="dormitory-index-item"
                    data-dormitory-id="{{ $dormitory->id }}"
                >
                    <a
                        href="{{ route('dormitories.show', $dormitory->id) }}"
                        class="block text-base font-semibold text-sky-700 hover:text-sky-800"
                        data-testid="dormitory-index-link"
                    >
                        {{ $dormitory->name }}
                    </a>
                    <p class="mt-1 text-sm text-slate-600" dir="ltr">{{ $dormitory->code }}</p>
                </li>
            @endforeach
        </ul>
    @endif
</div>
