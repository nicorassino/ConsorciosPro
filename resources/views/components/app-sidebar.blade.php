@props(['active' => null])

<aside {{ $attributes->merge(['class' => 'w-64 bg-primary-900 text-white flex flex-col shadow-2xl flex-shrink-0 z-20 hidden md:flex']) }}>
    <div class="px-3 py-2 flex items-center justify-center border-b border-primary-800/60">
        <div class="rounded-lg bg-white/95 px-2 py-1 shadow-md ring-1 ring-white/70">
            <img
                src="{{ asset('img/2.png') }}"
                alt="NR Sistemas"
                class="h-20 w-auto max-w-[220px] object-contain"
            >
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
                <span class="font-medium">Inicio</span>
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
                href="{{ route('gastos.index') }}"
                wire:navigate
                @class([
                    'flex items-center gap-3 px-3 py-2.5 rounded-lg group transition-colors',
                    'bg-accent-600/20 text-accent-500 border-r-4 border-accent-500' => $active === 'gastos',
                    'text-gray-300 hover:bg-primary-800 hover:text-white' => $active !== 'gastos',
                ])
            >
                <i class="fas fa-receipt text-lg @if($active !== 'gastos') group-hover:text-orange-400 @endif transition-colors"></i>
                <span class="font-medium">Gastos y Facturas</span>
            </a>

            <a
                href="{{ route('reportes.index') }}"
                wire:navigate
                @class([
                    'flex items-center gap-3 px-3 py-2.5 rounded-lg group transition-colors',
                    'bg-accent-600/20 text-accent-500 border-r-4 border-accent-500' => $active === 'reportes',
                    'text-gray-300 hover:bg-primary-800 hover:text-white' => $active !== 'reportes',
                ])
            >
                <i class="fas fa-chart-line text-lg @if($active !== 'reportes') group-hover:text-cyan-400 @endif transition-colors"></i>
                <span class="font-medium">Reportes y Conciliación</span>
            </a>
        </nav>
    </div>

</aside>
