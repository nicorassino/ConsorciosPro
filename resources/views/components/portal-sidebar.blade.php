@props(['active' => null])

<aside {{ $attributes->merge(['class' => 'w-64 bg-primary-900 text-white flex flex-col shadow-2xl flex-shrink-0 z-20 hidden md:flex']) }}>
    <div class="px-3 py-2 flex items-center justify-center border-b border-primary-800/60">
        <div class="flex flex-col items-center">
            <div class="rounded-lg bg-white/95 px-2 py-1 shadow-md ring-1 ring-white/70">
                <img
                    src="{{ asset('img/2.png') }}"
                    alt="NR Sistemas"
                    class="h-20 w-auto max-w-[220px] object-contain"
                >
            </div>
            <p class="mt-0.5 text-[10px] font-semibold text-primary-100/60 uppercase tracking-wider">Portal</p>
        </div>
    </div>

    <div class="p-4 flex-1 overflow-y-auto">
        <p class="text-xs font-semibold text-primary-100/50 uppercase tracking-wider mb-4 px-3">Mi cuenta</p>
        <nav class="space-y-1">
            @unless (request()->routeIs('portal.password.*'))
                <a
                    href="{{ route('portal.dashboard') }}"
                    @class([
                        'flex items-center gap-3 px-3 py-2.5 rounded-lg group transition-colors',
                        'bg-accent-600/20 text-accent-500 border-r-4 border-accent-500' => $active === 'dashboard',
                        'text-gray-300 hover:bg-primary-800 hover:text-white' => $active !== 'dashboard',
                    ])
                >
                    <i class="fas fa-home text-lg @if($active !== 'dashboard') group-hover:text-amber-400 @endif transition-colors"></i>
                    <span class="font-medium">Inicio</span>
                </a>
            @else
                <div class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-400">
                    <i class="fas fa-key text-lg"></i>
                    <span class="font-medium">Cambio de contraseña</span>
                </div>
            @endunless
        </nav>
    </div>

    @auth('portal')
        <div class="p-4 border-t border-primary-800/50 text-sm">
            <p class="text-gray-400 text-xs truncate mb-1" title="{{ auth('portal')->user()->email }}">{{ auth('portal')->user()->nombre }}</p>
            <form method="POST" action="{{ route('portal.logout') }}">
                @csrf
                <button type="submit" class="text-gray-300 hover:text-white text-left w-full">
                    <i class="fas fa-sign-out-alt mr-2"></i>Salir
                </button>
            </form>
        </div>
    @endauth

</aside>
