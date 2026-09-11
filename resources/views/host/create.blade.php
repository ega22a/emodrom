<x-layout title="Эмодром">
    <div class="mx-auto flex min-h-dvh max-w-md flex-col items-center justify-center px-6 py-12 text-center">
        <div class="flex size-24 items-center justify-center rounded-full bg-amber-500 text-white shadow-md">
            <x-icon name="bug" class="size-12" />
        </div>

        <h1 class="mt-8 text-4xl font-bold tracking-tight text-stone-900 sm:text-5xl">Эмодром</h1>
        <p class="mt-3 text-lg text-stone-500">Разберись со своими тараканами!</p>

        <p class="mx-auto mt-6 max-w-md text-balance text-stone-600">
            Создайте лобби прямо с телефона — здесь будет ваш пульт ведущего.
            Затем откройте отдельную ссылку на большом экране для игроков.
        </p>

        <form method="POST" action="{{ route('host.store') }}" class="mt-10">
            @csrf
            <button
                type="submit"
                class="inline-flex items-center gap-2 rounded-full bg-amber-500 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-amber-500/30 transition hover:bg-amber-600 active:scale-95"
            >
                <x-icon name="party-popper" class="size-6" />
                Создать лобби
            </button>
        </form>
    </div>
</x-layout>
