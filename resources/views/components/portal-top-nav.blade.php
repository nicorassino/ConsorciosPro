@props(['active' => null])

<header class="bg-primary-900 text-white shadow-md">
    <div class="mx-auto max-w-7xl px-4 sm:px-6">
        <div class="h-16 flex items-center justify-between gap-4">
            <div class="flex items-center gap-4 min-w-0">
                <div class="flex items-center gap-2 shrink-0">
                    <img
                        src="{{ asset('img/2.png') }}"
                        alt="NR Sistemas"
                        class="h-9 w-auto rounded-md bg-white p-1"
                    >
                </div>

                <nav class="hidden md:flex items-center gap-1 pl-3 border-l border-primary-800">
                    <a
                        href="{{ route('portal.dashboard') }}"
                        @class([
                            'px-3 py-2 rounded-lg text-sm font-medium transition-colors',
                            'bg-accent-600/30 text-white' => $active === 'dashboard',
                            'text-primary-100 hover:bg-primary-800' => $active !== 'dashboard',
                        ])
                    >
                        Inicio
                    </a>
                    <a
                        href="{{ route('portal.notes') }}"
                        @class([
                            'px-3 py-2 rounded-lg text-sm font-medium transition-colors',
                            'bg-accent-600/30 text-white' => $active === 'notes',
                            'text-primary-100 hover:bg-primary-800' => $active !== 'notes',
                        ])
                    >
                        Reglamento y notas
                    </a>
                    <a
                        href="{{ route('portal.contact') }}"
                        @class([
                            'px-3 py-2 rounded-lg text-sm font-medium transition-colors',
                            'bg-accent-600/30 text-white' => $active === 'contact',
                            'text-primary-100 hover:bg-primary-800' => $active !== 'contact',
                        ])
                    >
                        Contacto y encargado
                    </a>
                </nav>
            </div>

            @auth('portal')
                <div class="flex items-center gap-3 shrink-0">
                    <div class="hidden sm:block text-right">
                        <p class="text-xs text-primary-100/70">Hola</p>
                        <p class="text-sm font-semibold truncate max-w-40">{{ auth('portal')->user()->nombre }}</p>
                    </div>
                    <form method="POST" action="{{ route('portal.logout') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-primary-800 hover:bg-primary-700 px-3 py-2 text-sm font-medium transition-colors">
                            <i class="fas fa-sign-out-alt text-xs"></i>
                            Salir
                        </button>
                    </form>
                </div>
            @endauth
        </div>
    </div>

    <div class="md:hidden border-t border-primary-800">
        <div class="mx-auto max-w-7xl px-4 py-2 flex items-center gap-2 overflow-x-auto">
            <a
                href="{{ route('portal.dashboard') }}"
                @class([
                    'whitespace-nowrap px-3 py-2 rounded-lg text-sm font-medium transition-colors',
                    'bg-accent-600/30 text-white' => $active === 'dashboard',
                    'text-primary-100 hover:bg-primary-800' => $active !== 'dashboard',
                ])
            >
                Inicio
            </a>
            <a
                href="{{ route('portal.notes') }}"
                @class([
                    'whitespace-nowrap px-3 py-2 rounded-lg text-sm font-medium transition-colors',
                    'bg-accent-600/30 text-white' => $active === 'notes',
                    'text-primary-100 hover:bg-primary-800' => $active !== 'notes',
                ])
            >
                Reglamento y notas
            </a>
            <a
                href="{{ route('portal.contact') }}"
                @class([
                    'whitespace-nowrap px-3 py-2 rounded-lg text-sm font-medium transition-colors',
                    'bg-accent-600/30 text-white' => $active === 'contact',
                    'text-primary-100 hover:bg-primary-800' => $active !== 'contact',
                ])
            >
                Contacto y encargado
            </a>
        </div>
    </div>
</header>
