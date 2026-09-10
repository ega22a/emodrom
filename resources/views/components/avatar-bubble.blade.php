@props(['avatar', 'color', 'size' => 'md'])
@php
    $sizes = [
        'sm' => ['wrap' => 'size-8', 'icon' => 'size-4'],
        'md' => ['wrap' => 'size-12', 'icon' => 'size-6'],
        'lg' => ['wrap' => 'size-20', 'icon' => 'size-10'],
    ];
    $s = $sizes[$size] ?? $sizes['md'];
@endphp
<div {{ $attributes->merge(['class' => "inline-flex shrink-0 items-center justify-center rounded-full bg-{$color}-500 text-white shadow-sm {$s['wrap']}"]) }}>
    <x-icon :name="$avatar" :class="$s['icon']" />
</div>
