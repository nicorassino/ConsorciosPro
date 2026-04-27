<x-app-layout active="dashboard">
    <div class="flex flex-col h-full">
        <header class="bg-white shadow-sm z-10 flex-shrink-0">
            <div class="flex items-center justify-between px-6 py-4">
                <div class="flex items-center gap-4">
                    <button type="button" class="text-gray-500 hover:text-gray-700 md:hidden" aria-label="Menú">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <h2 class="text-2xl font-bold text-gray-800">Dashboard General</h2>
                </div>

                <div class="flex items-center gap-4">
                    <div class="relative">
                        <button type="button" class="text-gray-400 hover:text-accent-500 transition-colors relative" aria-label="Notificaciones">
                            <i class="fas fa-bell text-xl"></i>
                            <span class="absolute -top-1 -right-1 h-4 w-4 bg-rose-500 rounded-full text-[10px] text-white flex items-center justify-center font-bold border-2 border-white">3</span>
                        </button>
                    </div>
                    <div class="h-8 w-8 rounded-full bg-accent-100 flex items-center justify-center text-accent-600 font-bold border border-accent-200 text-sm">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-6 bg-gray-50 min-h-0">
            <div class="max-w-7xl mx-auto space-y-6">
                <div class="bg-gradient-to-r from-primary-900 to-accent-600 rounded-2xl p-8 text-white shadow-lg relative overflow-hidden">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-white opacity-10 rounded-full blur-2xl"></div>
                    <div class="relative z-10">
                        <h3 class="text-3xl font-bold mb-2">¡Hola, {{ auth()->user()->name }}!</h3>
                        <p class="text-primary-100 text-lg">Resumen del sistema. Desde el menú podés ir a Consorcios y el resto de módulos.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <a href="{{ route('consorcios.index') }}" wire:navigate class="glass-card rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow group relative overflow-hidden border-l-4 border-amber-400 block">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm font-medium text-gray-500 mb-1">Consorcios</p>
                                <h4 class="text-3xl font-bold text-gray-800">ABM</h4>
                            </div>
                            <div class="h-12 w-12 rounded-lg bg-amber-50 text-amber-500 flex items-center justify-center group-hover:bg-amber-500 group-hover:text-white transition-colors">
                                <i class="fas fa-building text-xl"></i>
                            </div>
                        </div>
                        <p class="mt-4 text-sm text-gray-500">Gestionar consorcios</p>
                    </a>

                    <a href="{{ route('unidades.index') }}" wire:navigate class="glass-card rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow group relative overflow-hidden border-l-4 border-blue-400 block">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm font-medium text-gray-500 mb-1">Unidades</p>
                                <h4 class="text-3xl font-bold text-gray-800">ABM</h4>
                            </div>
                            <div class="h-12 w-12 rounded-lg bg-blue-50 text-blue-500 flex items-center justify-center group-hover:bg-blue-500 group-hover:text-white transition-colors">
                                <i class="fas fa-door-open text-xl"></i>
                            </div>
                        </div>
                        <p class="mt-4 text-sm text-gray-500">Gestionar unidades funcionales</p>
                    </a>

                    <a href="{{ route('presupuestos.index') }}" wire:navigate class="glass-card rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow group relative overflow-hidden border-l-4 border-purple-400 block">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm font-medium text-gray-500 mb-1">Presupuestos</p>
                                <h4 class="text-3xl font-bold text-gray-800">ABM</h4>
                            </div>
                            <div class="h-12 w-12 rounded-lg bg-purple-50 text-purple-500 flex items-center justify-center group-hover:bg-purple-500 group-hover:text-white transition-colors">
                                <i class="fas fa-calculator text-xl"></i>
                            </div>
                        </div>
                        <p class="mt-4 text-sm text-gray-500">Crear y administrar presupuestos</p>
                    </a>

                    <div class="glass-card rounded-xl p-6 shadow-sm border-l-4 border-emerald-400 opacity-75">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm font-medium text-gray-500 mb-1">Liquidaciones</p>
                                <h4 class="text-xl font-bold text-gray-600">Próximamente</h4>
                            </div>
                            <div class="h-12 w-12 rounded-lg bg-emerald-50 text-emerald-400 flex items-center justify-center">
                                <i class="fas fa-file-invoice-dollar text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</x-app-layout>
