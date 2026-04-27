@props(['active' => null])

<aside {{ $attributes->merge(['class' => 'w-64 bg-primary-900 text-white flex flex-col shadow-2xl flex-shrink-0 z-20 hidden md:flex']) }}>
    <div class="p-6 flex items-center justify-center border-b border-primary-800/50">
        <div class="flex flex-col items-center">
            <div class="h-10 w-10 bg-white rounded-lg flex items-center justify-center mb-2 shadow-md text-primary-900 font-bold text-xl">CP</div>
            <h1 class="text-xl font-bold tracking-wider text-white">Consorcios<span class="text-accent-500">Pro</span></h1>
        </div>
    </div>

    <div class="p-4 flex-1 overflow-y-auto">
        <p class="text-xs font-semibold text-primary-100/50 uppercase tracking-wider mb-4 px-3">Principal</p>
        <nav class="space-y-1">
            <a
                href="{{ route('dashboard') }}"
                wire:navigate
                @class([
                    'flex items-center gap-3 px-3 py-2.5 rounded-lg group transition-colors',
                    'bg-accent-600/20 text-accent-500 border-r-4 border-accent-500' => $active === 'dashboard',
                    'text-gray-300 hover:bg-primary-800 hover:text-white' => $active !== 'dashboard',
                ])
            >
                <i class="fas fa-home text-lg @if($active !== 'dashboard') group-hover:text-amber-400 @endif transition-colors"></i>
                <span class="font-medium">Dashboard</span>
            </a>

            <a
                href="{{ route('consorcios.index') }}"
                wire:navigate
                @class([
                    'flex items-center gap-3 px-3 py-2.5 rounded-lg group transition-colors',
                    'bg-accent-600/20 text-accent-500 border-r-4 border-accent-500' => $active === 'consorcios',
                    'text-gray-300 hover:bg-primary-800 hover:text-white' => $active !== 'consorcios',
                ])
            >
                <i class="fas fa-building text-lg @if($active !== 'consorcios') group-hover:text-amber-400 @endif transition-colors"></i>
                <span class="font-medium">Consorcios</span>
            </a>

            <a
                href="{{ route('unidades.index') }}"
                wire:navigate
                @class([
                    'flex items-center gap-3 px-3 py-2.5 rounded-lg group transition-colors',
                    'bg-accent-600/20 text-accent-500 border-r-4 border-accent-500' => $active === 'unidades',
                    'text-gray-300 hover:bg-primary-800 hover:text-white' => $active !== 'unidades',
                ])
            >
                <i class="fas fa-door-open text-lg @if($active !== 'unidades') group-hover:text-blue-400 @endif transition-colors"></i>
                <span class="font-medium">Unidades Funcionales</span>
            </a>

            <div class="mt-8 mb-4">
                <p class="text-xs font-semibold text-primary-100/50 uppercase tracking-wider px-3">Gestión Financiera</p>
            </div>

            <a
                href="{{ route('presupuestos.index') }}"
                wire:navigate
                @class([
                    'flex items-center gap-3 px-3 py-2.5 rounded-lg group transition-colors',
                    'bg-accent-600/20 text-accent-500 border-r-4 border-accent-500' => $active === 'presupuestos',
                    'text-gray-300 hover:bg-primary-800 hover:text-white' => $active !== 'presupuestos',
                ])
            >
                <i class="fas fa-calculator text-lg @if($active !== 'presupuestos') group-hover:text-purple-400 @endif transition-colors"></i>
                <span class="font-medium">Presupuestos</span>
            </a>

            <a
                href="{{ route('liquidaciones.index') }}"
                wire:navigate
                @class([
                    'flex items-center gap-3 px-3 py-2.5 rounded-lg group transition-colors',
                    'bg-accent-600/20 text-accent-500 border-r-4 border-accent-500' => $active === 'liquidaciones',
                    'text-gray-300 hover:bg-primary-800 hover:text-white' => $active !== 'liquidaciones',
                ])
            >
                <i class="fas fa-file-invoice-dollar text-lg @if($active !== 'liquidaciones') group-hover:text-emerald-400 @endif transition-colors"></i>
                <span class="font-medium">Liquidaciones</span>
            </a>

            <a
                href="#"
                class="flex items-center gap-3 px-3 py-2.5 text-gray-300 hover:bg-primary-800 hover:text-white rounded-lg group transition-colors opacity-70 pointer-events-none cursor-not-allowed"
                title="Próximamente"
            >
                <i class="fas fa-receipt text-lg group-hover:text-orange-400 transition-colors"></i>
                <span class="font-medium">Gastos y Facturas</span>
            </a>
        </nav>
    </div>

    @auth
        <div class="p-4 border-t border-primary-800/50 text-sm">
            <p class="text-gray-400 text-xs truncate mb-1" title="{{ auth()->user()->email }}">{{ auth()->user()->name }}</p>
            <a href="{{ route('profile') }}" wire:navigate class="block text-gray-300 hover:text-white mb-2">
                <i class="fas fa-user-circle mr-2"></i>Perfil
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-gray-300 hover:text-white text-left w-full">
                    <i class="fas fa-sign-out-alt mr-2"></i>Salir
                </button>
            </form>
        </div>
    @endauth

    <div class="p-4 border-t border-primary-800/50 bg-primary-800/20 flex justify-center">
        <img
            src="{{ asset('img/logo_cliente.png') }}"
            alt="Cliente"
            class="h-20 w-auto max-w-[95%] object-contain bg-white rounded-xl p-3 shadow-sm hover:scale-105 transition-transform"
        >
    </div>
</aside>
