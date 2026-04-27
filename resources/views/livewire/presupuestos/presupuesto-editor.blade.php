<div class="flex flex-col h-full">
    <header class="bg-white shadow-sm z-10 flex-shrink-0">
        <div class="flex items-center justify-between px-6 py-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('presupuestos.index') }}" wire:navigate class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h2 class="text-2xl font-bold text-gray-800">
                    {{ $isCreateMode ? 'Nuevo Presupuesto' : 'Presupuesto Mensual' }}
                </h2>
            </div>
            @if ($presupuesto)
                @if ($canEdit)
                    <button type="button" wire:click="finalize" wire:confirm="¿Finalizar este presupuesto? Luego se abrirá en modo solo lectura." class="bg-primary-900 hover:bg-primary-800 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                        <i class="fas fa-check-double mr-1"></i> Finalizar
                    </button>
                @else
                    <span class="text-xs font-semibold bg-gray-100 text-gray-700 px-3 py-2 rounded-full">Solo lectura</span>
                @endif
            @endif
        </div>
    </header>

    <main class="flex-1 overflow-y-auto p-6 bg-gray-50 min-h-0">
        <div class="max-w-7xl mx-auto space-y-6">
            @if (session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            @if ($isCreateMode)
                <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm max-w-2xl">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Crear presupuesto por mes/año</h3>
                    <form wire:submit.prevent="createPresupuesto" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Consorcio *</label>
                            <select wire:model="consorcio_id" class="w-full p-2 border border-gray-300 rounded-lg outline-none">
                                <option value="">Seleccionar consorcio...</option>
                                @foreach ($consorcios as $consorcio)
                                    <option value="{{ $consorcio->id }}">{{ $consorcio->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mes/Año *</label>
                            <input type="month" wire:model="periodo" class="w-full p-2 border border-gray-300 rounded-lg outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notas</label>
                            <textarea wire:model="notas" rows="3" class="w-full p-2 border border-gray-300 rounded-lg outline-none"></textarea>
                        </div>
                        <div class="pt-2 flex justify-end">
                            <button type="submit" class="px-4 py-2 bg-accent-600 rounded-lg text-white font-medium">
                                Crear y abrir presupuesto
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">
                                {{ $presupuesto->consorcio->nombre }} - {{ $presupuesto->periodo->translatedFormat('F Y') }}
                            </h3>
                            <p class="text-sm text-gray-500 mt-1">Estado: <span class="font-semibold">{{ $presupuesto->estado->label() }}</span></p>
                        </div>
                    </div>
                    @if ($presupuesto->notas)
                        <p class="text-sm text-gray-600 mt-3">{{ $presupuesto->notas }}</p>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white rounded-xl p-4 border-t-4 border-emerald-500 shadow-sm">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Ordinario</p>
                        <h4 class="text-2xl font-bold text-gray-800 mt-1">${{ number_format($totalOrdinario, 2, ',', '.') }}</h4>
                    </div>
                    <div class="bg-white rounded-xl p-4 border-t-4 border-amber-500 shadow-sm">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Extraordinario</p>
                        <h4 class="text-2xl font-bold text-gray-800 mt-1">${{ number_format($totalExtraordinario, 2, ',', '.') }}</h4>
                    </div>
                    <div class="bg-primary-900 rounded-xl p-4 shadow-md text-white">
                        <p class="text-xs font-semibold text-white/70 uppercase tracking-wide">Total General</p>
                        <h4 class="text-2xl font-bold mt-1">${{ number_format($totalGeneral, 2, ',', '.') }}</h4>
                    </div>
                </div>

                <div class="glass-card rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-4 border-b border-gray-100 bg-white flex items-center justify-between">
                        <h3 class="font-bold text-gray-700">Conceptos del Mes</h3>
                        @if ($canEdit)
                            <button type="button" wire:click="openCreateConceptModal" class="bg-gray-800 hover:bg-gray-900 text-white px-3 py-2 rounded-lg text-sm font-medium">
                                <i class="fas fa-plus mr-1"></i> Agregar Concepto
                            </button>
                        @endif
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-200">
                                <tr>
                                    <th class="p-3 text-left">Concepto</th>
                                    <th class="p-3 text-left">Tipo</th>
                                    <th class="p-3 text-left">Rubro</th>
                                    <th class="p-3 text-right">Monto Estimado</th>
                                    <th class="p-3 text-center">Cuotas</th>
                                    <th class="p-3 text-left">Estado Factura</th>
                                    <th class="p-3 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($presupuesto->conceptos as $concepto)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="p-3">
                                            <p class="font-semibold text-gray-800">{{ $concepto->nombre }}</p>
                                            @if ($concepto->descripcion)
                                                <p class="text-xs text-gray-500 mt-1">{{ $concepto->descripcion }}</p>
                                            @endif
                                        </td>
                                        <td class="p-3">
                                            <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $concepto->tipo->value === 'ordinario' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                                {{ $concepto->tipo->label() }}
                                            </span>
                                        </td>
                                        <td class="p-3">{{ $concepto->rubro->label() }}</td>
                                        <td class="p-3 text-right font-mono">${{ number_format((float) $concepto->monto_total, 2, ',', '.') }}</td>
                                        <td class="p-3 text-center">
                                            @if ($concepto->cuotas_total === 1)
                                                <span class="text-xs text-gray-500">Única</span>
                                            @else
                                                <span class="text-xs font-semibold bg-purple-100 text-purple-700 px-2 py-1 rounded-full">
                                                    {{ $concepto->cuota_actual }}/{{ $concepto->cuotas_total }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="p-3">
                                            @if ($concepto->monto_factura_real !== null)
                                                <span class="text-xs font-semibold bg-blue-100 text-blue-700 px-2 py-1 rounded-full">Confirmada</span>
                                            @else
                                                <span class="text-xs font-semibold bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full">Pendiente</span>
                                            @endif
                                        </td>
                                        <td class="p-3 text-right whitespace-nowrap">
                                            @if ($canEdit)
                                                <button type="button" wire:click="openEditConceptModal({{ $concepto->id }})" class="text-blue-500 hover:text-blue-700 p-2" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" wire:click="deleteConcept({{ $concepto->id }})" wire:confirm="¿Eliminar este concepto?" class="text-rose-500 hover:text-rose-700 p-2" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @else
                                                <span class="text-xs text-gray-400">Bloqueado</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="p-8 text-center text-gray-500">Este presupuesto no tiene conceptos.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </main>

    @if ($showConceptModal && $presupuesto)
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4" wire:click.self="closeConceptModal">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl">
                <div class="p-5 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-800">{{ $editingConceptId ? 'Editar Concepto' : 'Agregar Concepto' }}</h3>
                    <button type="button" wire:click="closeConceptModal" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                </div>
                <form wire:submit.prevent="saveConcept" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                        <input type="text" wire:model="concepto_nombre" class="w-full p-2 border border-gray-300 rounded-lg outline-none">
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
                            <select wire:model="concepto_tipo" class="w-full p-2 border border-gray-300 rounded-lg outline-none">
                                @foreach ($tiposConcepto as $tipo)
                                    <option value="{{ $tipo->value }}">{{ $tipo->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Rubro *</label>
                            <select wire:model="concepto_rubro" class="w-full p-2 border border-gray-300 rounded-lg outline-none">
                                @foreach ($rubrosConcepto as $rubro)
                                    <option value="{{ $rubro->value }}">{{ $rubro->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Monto *</label>
                            <input type="number" min="0" step="0.01" wire:model="concepto_monto_total" class="w-full p-2 border border-gray-300 rounded-lg outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cuotas total</label>
                            <input type="number" min="1" max="60" wire:model="concepto_cuotas_total" class="w-full p-2 border border-gray-300 rounded-lg outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cuota actual</label>
                            <input type="number" min="1" max="60" wire:model="concepto_cuota_actual" class="w-full p-2 border border-gray-300 rounded-lg outline-none">
                        </div>
                        <div class="flex items-end">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" wire:model="concepto_aplica_cocheras" class="h-4 w-4 rounded border-gray-300 text-accent-600 focus:ring-accent-500">
                                Aplica cocheras
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                        <textarea wire:model="concepto_descripcion" rows="3" class="w-full p-2 border border-gray-300 rounded-lg outline-none"></textarea>
                    </div>
                    <div class="pt-2 flex justify-end gap-2">
                        <button type="button" wire:click="closeConceptModal" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700">Cancelar</button>
                        <button type="submit" class="px-4 py-2 bg-gray-800 rounded-lg text-white font-medium">{{ $editingConceptId ? 'Guardar cambios' : 'Agregar concepto' }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
