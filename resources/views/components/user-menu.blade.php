<div
    class="relative z-30"
    x-data="{ open: false }"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
>
    <button
        type="button"
        class="h-8 w-8 rounded-full bg-accent-100 flex items-center justify-center text-accent-600 font-bold border border-accent-200 text-sm hover:bg-accent-200 transition-colors cursor-pointer"
        @click="open = !open"
        aria-label="Menú de usuario"
        :aria-expanded="open.toString()"
    >
        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
    </button>

    <div
        x-cloak
        x-show="open"
        x-transition.origin.top.right
        class="absolute right-0 top-full pt-2 w-56 z-[9999]"
    >
        <div class="rounded-xl border border-gray-200 bg-white shadow-lg p-2">
            <div class="px-3 py-2 border-b border-gray-100 mb-1">
                <p class="text-sm font-semibold text-gray-800 truncate" title="{{ auth()->user()->name }}">{{ auth()->user()->name }}</p>
                <p class="text-xs text-gray-500 truncate" title="{{ auth()->user()->email }}">{{ auth()->user()->email }}</p>
            </div>

            <a
                href="{{ route('profile') }}"
                class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
            >
                <i class="fas fa-user-circle text-gray-500"></i>
                Perfil
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button
                    type="submit"
                    class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm text-red-600 hover:bg-red-50"
                >
                    <i class="fas fa-sign-out-alt"></i>
                    Salir
                </button>
            </form>
        </div>
    </div>
</div>
