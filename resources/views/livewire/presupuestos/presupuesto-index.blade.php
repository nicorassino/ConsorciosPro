<div class="flex flex-col h-full">
    <header class="bg-white shadow-sm z-10 flex-shrink-0">
        <div class="flex items-center justify-between px-6 py-4">
            <h2 class="text-2xl font-bold text-gray-800">Presupuestos Mensuales</h2>
            <a href="{{ route('presupuestos.create') }}" wire:navigate class="bg-accent-600 hover:bg-accent-700 text-white px-4 py-2 rounded-lg font-medium shadow-sm transition-colors inline-flex items-center gap-2">
                <i class="fas fa-plus"></i> Nuevo Presupuesto
            </a>
        </div>
    </header>

    <main class="flex-1 overflow-y-auto p-6 bg-gray-50 min-h-0">
        <div class="max-w-7xl mx-auto space-y-6">
            @if (session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="glass-card rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-4 bg-white grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Consorcio</label>
                        <select wire:model.live="consorcioFilter" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-500 outline-none">
                            <option value="">Todos</option>
                            @foreach ($consorcios as $consorcio)
                                <option value="{{ $consorcio->id }}">{{ $consorcio->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Estado</label>
                        <select wire:model.live="estadoFilter" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-500 outline-none">
                            <option value="">Todos</option>
                            @foreach ($estados as $estado)
                                <option value="{{ $estado->value }}">{{ $estado->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Período</label>
                        <input type="month" wire:model.live="periodoFilter" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-500 outline-none">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Buscar</label>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre de consorcio..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-500 outline-none">
                        </div>
                    </div>
                </div>
            </div>

            <div class="glass-card rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-200">
                            <tr>
                                <th class="p-3 text-left">Consorcio</th>
                                <th class="p-3 text-left">Período</th>
                                <th class="p-3 text-left">Estado</th>
                                <th class="p-3 text-center">Conceptos</th>
                                <th class="p-3 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($presupuestos as $presupuesto)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="p-3 font-semibold text-gray-800">{{ $presupuesto->consorcio->nombre }}</td>
                                    <td class="p-3 text-gray-700">{{ $presupuesto->periodo->translatedFormat('F Y') }}</td>
                                    <td class="p-3">
                                        <span class="text-xs font-bold px-2 py-1 rounded-full
                                            {{ $presupuesto->estado->value === 'borrador' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $presupuesto->estado->value === 'finalizado' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $presupuesto->estado->value === 'liquidado' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                        ">
                                            {{ $presupuesto->estado->label() }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-center text-gray-600">{{ $presupuesto->conceptos_count }}</td>
                                    <td class="p-3 text-right">
                                        <a href="{{ route('presupuestos.show', $presupuesto->id) }}" wire:navigate class="text-sm font-semibold text-blue-600 hover:text-blue-800">
                                            {{ $presupuesto->estado->value === 'borrador' ? 'Abrir edición' : 'Abrir lectura' }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-8 text-center text-gray-500">No hay presupuestos cargados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($presupuestos->hasPages())
                    <div class="px-4 py-3 border-t border-gray-100 bg-white">
                        {{ $presupuestos->links() }}
                    </div>
                @endif
            </div>
        </div>
    </main>
</div>
