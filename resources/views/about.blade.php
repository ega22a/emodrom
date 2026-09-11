<x-layout title="Об игре — Эмодром">
    <div class="mx-auto flex min-h-dvh max-w-2xl flex-col px-6 py-12">
        <a href="{{ route('host.create') }}" class="inline-flex items-center gap-1 text-sm font-medium text-stone-500 hover:text-stone-700">
            <x-icon name="compass" class="size-4" /> На главную
        </a>

        <h1 class="mt-6 text-3xl font-bold text-stone-900">Об игре</h1>

        <section class="mt-8">
            <h2 class="flex items-center gap-2 text-lg font-semibold text-stone-800">
                <x-icon name="drama" class="size-5 text-amber-500" /> Вдохновение
            </h2>
            <p class="mt-3 text-stone-600">
                «Эмодром» — переосмысление механик игры «Лас Кукарачас»: один игрок (чтец) тайно
                выбирает эмоцию, остальные честно отвечают, что чувствовали бы сами в предложенной
                ситуации, а совпадения с чтецом приносят «искры». Если никто не угадал — награду
                забирает себе чтец. Мы также взяли идею «допроса»: ведущий может по очереди
                расспросить тех, кто ответил не так, как большинство, и начислить им искры за
                интересный ответ.
            </p>
        </section>

        @php $music = config('credits.music.lobby'); @endphp
        <section class="mt-10">
            <h2 class="flex items-center gap-2 text-lg font-semibold text-stone-800">
                <x-icon name="music-2" class="size-5 text-amber-500" /> Музыка
            </h2>
            <p class="mt-3 text-stone-600">
                Фоновая музыка лобби — «{{ $music['sound_name'] }}» автора
                <a href="{{ $music['author_url'] }}" target="_blank" rel="noopener" class="font-medium text-amber-600 underline">{{ $music['author_name'] }}</a>,
                с <a href="{{ $music['sound_url'] }}" target="_blank" rel="noopener" class="underline">freesound.org</a>,
                распространяется по лицензии
                <a href="{{ $music['license_url'] }}" target="_blank" rel="noopener" class="underline">{{ $music['license_name'] }}</a>.
            </p>
        </section>

        <section class="mt-10">
            <h2 class="flex items-center gap-2 text-lg font-semibold text-stone-800">
                <x-icon name="zap" class="size-5 text-amber-500" /> Звуковые эффекты
            </h2>
            <p class="mt-3 text-stone-600">Все с freesound.org:</p>
            <ul class="mt-3 space-y-2 text-sm text-stone-600">
                @foreach (config('credits.sfx') as $sfx)
                    <li>
                        «{{ $sfx['sound_name'] }}» —
                        <a href="{{ $sfx['author_url'] }}" target="_blank" rel="noopener" class="font-medium text-amber-600 underline">{{ $sfx['author_name'] }}</a>,
                        <a href="{{ $sfx['sound_url'] }}" target="_blank" rel="noopener" class="underline">источник</a>,
                        <a href="{{ $sfx['license_url'] }}" target="_blank" rel="noopener" class="underline">{{ $sfx['license_name'] }}</a>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>
</x-layout>
