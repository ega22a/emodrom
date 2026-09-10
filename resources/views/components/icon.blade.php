@props(['name', 'class' => 'size-6'])

<svg {{ $attributes->merge(['class' => $class]) }} fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    <use href="#icon-{{ \App\Support\IconLibrary::sanitize($name) }}"></use>
</svg>
