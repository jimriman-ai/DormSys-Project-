@props([
    'type' => 'info',
    'message' => '',
])

@php
    $styles = match ($type) {
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'error' => 'border-rose-200 bg-rose-50 text-rose-800',
        default => 'border-slate-200 bg-slate-50 text-slate-800',
    };
@endphp

<div {{ $attributes->merge(['class' => "rounded-lg border px-4 py-3 text-sm {$styles}"]) }}>
    {{ $message }}
</div>
