@props([
    'label' => null,
    'for' => null,
    'error' => null,
])

<div {{ $attributes->merge(['class' => 'space-y-1']) }}>
    @if ($label)
        <label @if($for) for="{{ $for }}" @endif class="block text-sm font-medium text-slate-700">
            {{ $label }}
        </label>
    @endif

    {{ $slot }}

    @if ($error)
        <p class="text-sm text-rose-600">{{ $error }}</p>
    @endif
</div>
