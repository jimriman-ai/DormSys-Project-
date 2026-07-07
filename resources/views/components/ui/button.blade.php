@props([
    'variant' => 'primary',
    'type' => 'button',
])

@php
    $styles = match ($variant) {
        'secondary' => 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50',
        'danger' => 'bg-rose-600 text-white hover:bg-rose-700',
        default => 'bg-sky-700 text-white hover:bg-sky-800',
    };
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => "inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-medium transition disabled:cursor-not-allowed disabled:opacity-60 {$styles}"]) }}
>
    {{ $slot }}
</button>
