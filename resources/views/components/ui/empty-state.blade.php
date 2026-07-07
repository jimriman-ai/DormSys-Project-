@props([
    'title' => 'موردی یافت نشد',
    'description' => 'هنوز رکوردی برای نمایش وجود ندارد.',
])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center']) }}>
    <h2 class="text-lg font-medium text-slate-900">{{ $title }}</h2>
    <p class="mt-2 text-sm text-slate-600">{{ $description }}</p>

    @if (isset($action))
        <div class="mt-6">
            {{ $action }}
        </div>
    @endif
</div>
