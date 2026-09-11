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

        <section class="mt-10">
            <h2 class="flex items-center gap-2 text-lg font-semibold text-stone-800">
                <x-icon name="music-2" class="size-5 text-amber-500" /> Музыка
            </h2>
            <p class="mt-3 text-stone-600">
                Фоновая музыка лобби — «{{ $musicCredit['sound_name'] }}» автора
                <a href="{{ $musicCredit['author_url'] }}" target="_blank" rel="noopener" class="font-medium text-amber-600 underline">{{ $musicCredit['author_name'] }}</a>,
                с <a href="{{ $musicCredit['sound_url'] }}" target="_blank" rel="noopener" class="underline">freesound.org</a>,
                распространяется по лицензии
                <a href="{{ $musicCredit['license_url'] }}" target="_blank" rel="noopener" class="underline">{{ $musicCredit['license_name'] }}</a>.
            </p>
        </section>
    </div>
</x-layout>
