@props(['title' => 'Эмодром'])
<!DOCTYPE html>
<html lang="ru" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('scripts')
</head>
<body class="min-h-dvh bg-orange-50 font-sans text-stone-900 antialiased">
    <svg class="hidden" aria-hidden="true">{!! \App\Support\IconLibrary::spriteSymbols() !!}</svg>

    {{ $slot }}
</body>
</html>
