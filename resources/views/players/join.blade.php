<x-layout title="Присоединиться — Лас Кукарачас">
    <div class="mx-auto flex min-h-dvh max-w-md flex-col px-5 py-8">
        <div class="text-center">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold tracking-wide text-amber-700">
                Лобби {{ $lobby->code }}
            </span>
            <h1 class="mt-4 text-3xl font-bold text-stone-900">Присоединиться</h1>
            <p class="mt-1 text-stone-500">Придумайте имя и выберите себе таракана</p>
        </div>

        <form
            method="POST"
            action="{{ route('lobbies.join.store', $lobby) }}"
            class="mt-8 flex flex-1 flex-col gap-6"
            x-data="{ avatar: '', name: '' }"
        >
            @csrf

            <div>
                <label for="name" class="mb-1.5 block text-sm font-medium text-stone-700">Ваше имя</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    maxlength="20"
                    required
                    x-model="name"
                    placeholder="Например, Аня"
                    class="w-full rounded-xl border border-stone-300 px-4 py-3 text-base text-stone-900 shadow-sm outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-200"
                >
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex-1">
                <span class="mb-1.5 block text-sm font-medium text-stone-700">Выберите аватар</span>
                <input type="hidden" name="avatar" x-model="avatar">

                <div class="grid grid-cols-5 gap-3 sm:grid-cols-6">
                    @foreach ($avatars as $option)
                        <button
                            type="button"
                            @click="avatar = '{{ $option->key }}'"
                            class="rounded-full p-0.5 transition"
                            :class="avatar === '{{ $option->key }}' ? 'ring-2 ring-offset-2 ring-{{ $option->color }}-500' : ''"
                        >
                            <x-avatar-bubble :avatar="$option->icon" :color="$option->color" size="md" />
                        </button>
                    @endforeach
                </div>
                @error('avatar')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="submit"
                :disabled="!name || !avatar"
                :class="(!name || !avatar) ? 'opacity-40' : 'active:scale-95'"
                class="w-full rounded-full bg-amber-500 py-4 text-lg font-semibold text-white shadow-lg shadow-amber-500/30 transition"
            >
                Присоединиться
            </button>
        </form>
    </div>
</x-layout>
