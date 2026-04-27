<div class="flex flex-col h-full">
    <header class="bg-white shadow-sm z-10 flex-shrink-0">
        <div class="flex items-center justify-between px-6 py-4">
            <h2 class="text-2xl font-bold text-gray-800">Consorcios</h2>
            <button
                type="button"
                wire:click="openCreateModal"
                class="bg-accent-600 hover:bg-accent-700 text-white px-4 py-2 rounded-lg font-medium shadow-sm transition-colors flex items-center gap-2"
            >
                <i class="fas fa-plus"></i> Nuevo Consorcio
            </button>
        </div>
    </header>

    <main class="flex-1 overflow-y-auto p-6 bg-gray-50 min-h-0">
        <div class="max-w-7xl mx-auto">
            @if (session('status'))
                <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="glass-card rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-4 border-b border-gray-100 bg-white flex justify-between items-center gap-4">
                    <div class="relative flex-1 max-w-md">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Buscar por nombre, CUIT o dirección..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-500 focus:border-accent-500 outline-none transition-all"
                        >
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                                <th class="p-4 font-medium">Consorcio</th>
                                <th class="p-4 font-medium">Unidades</th>
                                <th class="p-4 font-medium">Banco</th>
                                <th class="p-4 font-medium text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($consorcios as $c)
                                <tr class="hover:bg-gray-50 transition-colors" wire:key="consorcio-row-{{ $c->id }}">
                                    <td class="p-4">
                                        <div class="font-bold text-gray-800">{{ $c->nombre }}</div>
                                        <div class="text-sm text-gray-500">CUIT: {{ $c->cuit }} • {{ $c->direccion }}</div>
                                    </td>
                                    <td class="p-4 text-gray-600">{{ $c->unidades_count }} u.f.</td>
                                    <td class="p-4 text-gray-600">{{ $c->banco ?: '—' }}</td>
                                    <td class="p-4 text-right whitespace-nowrap">
                                        <button
                                            type="button"
                                            wire:click="openEditModal({{ $c->id }})"
                                            class="text-blue-500 hover:text-blue-700 p-2"
                                            title="Editar"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="delete({{ $c->id }})"
                                            wire:confirm="¿Eliminar este consorcio? Esta acción puede deshacerse solo desde la base de datos (borrado lógico)."
                                            class="text-rose-500 hover:text-rose-700 p-2"
                                            title="Eliminar"
                                        >
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-8 text-center text-gray-500">
                                        No hay consorcios cargados. Creá el primero con «Nuevo Consorcio».
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($consorcios->hasPages())
                    <div class="px-4 py-3 border-t border-gray-100 bg-white">
                        {{ $consorcios->links() }}
                    </div>
                @endif
            </div>
        </div>
    </main>

    @if ($showModal)
        <div
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
            wire:click.self="closeModal"
            wire:key="modal-consorcio"
        >
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center sticky top-0 bg-white z-10">
                    <h3 class="text-xl font-bold text-gray-800">
                        {{ $editingId ? 'Editar Consorcio' : 'Nuevo Consorcio' }}
                    </h3>
                    <button type="button" wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form wire:submit.prevent="save" class="p-6 space-y-8">
                    @if ($errors->any())
                        <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                            <p class="font-semibold mb-1">No se pudo guardar. Revisá estos campos:</p>
                            <ul class="list-disc pl-5 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div>
                        <h4 class="text-lg font-semibold text-primary-900 border-b pb-2 mb-4">Datos Generales</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del Consorcio *</label>
                                <input type="text" wire:model="nombre" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-500 outline-none" placeholder="Ej: Torre Los Andes">
                                @error('nombre') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">CUIT *</label>
                                <input type="text" wire:model="cuit" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-500 outline-none" placeholder="XX-XXXXXXXX-X">
                                @error('cuit') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Dirección *</label>
                                <input type="text" wire:model="direccion" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-500 outline-none">
                                @error('direccion') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-lg font-semibold text-primary-900 border-b pb-2 mb-4">Datos Bancarios</h4>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Banco</label>
                                <input type="text" wire:model="banco" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-500 outline-none">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">CBU</label>
                                <input type="text" wire:model="cbu" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-500 outline-none" placeholder="22 dígitos">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nro Cuenta Bancaria</label>
                                <input type="text" wire:model="nro_cuenta_bancaria" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Sucursal</label>
                                <input type="text" wire:model="sucursal" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Convenio</label>
                                <input type="text" wire:model="convenio" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Dígito Verificador</label>
                                <input type="text" wire:model="digito_verificador" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-500 outline-none">
                            </div>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-lg font-semibold text-primary-900 border-b pb-2 mb-4">Identificación Legal y Catastral</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nro Matrícula</label>
                                <input type="text" wire:model="nro_matricula" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Inscripción Reglamento</label>
                                <input type="date" wire:model="fecha_inscripcion_reglamento" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Unidad Facturación Aguas</label>
                                <input type="text" wire:model="unidad_facturacion_aguas" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nro Cuenta Rentas</label>
                                <input type="text" wire:model="nro_cuenta_rentas" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-500 outline-none">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nomenclatura Catastral</label>
                                <input type="text" wire:model="nomenclatura_catastral" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-500 outline-none">
                            </div>
                            <div class="md:col-span-3">
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" wire:model="tiene_cocheras" class="h-4 w-4 rounded border-gray-300 text-accent-600 focus:ring-accent-500">
                                    El consorcio tiene cocheras
                                </label>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-lg font-semibold text-primary-900 border-b pb-2 mb-4">Encargado del Edificio</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                                <input type="text" wire:model="encargado_nombre" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Apellido</label>
                                <input type="text" wire:model="encargado_apellido" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                                <input type="text" wire:model="encargado_telefono" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-500 outline-none">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Horarios de Atención</label>
                                <textarea wire:model="encargado_horarios" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-500 outline-none text-sm" rows="2"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Días</label>
                                <input type="text" wire:model="encargado_dias" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-500 outline-none" placeholder="Lun a Vie, Sábados...">
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Empresa de Servicio</label>
                                <input type="text" wire:model="encargado_empresa_servicio" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-500 outline-none">
                            </div>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-lg font-semibold text-primary-900 border-b pb-2 mb-4">Configuración de Cupón SIRO</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre Administración</label>
                                <input type="text" wire:model="nombre_administracion" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Condición IVA</label>
                                <select wire:model="condicion_iva" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-500 outline-none">
                                    @foreach (\App\Enums\CondicionIvaConsorcio::cases() as $iva)
                                        <option value="{{ $iva->value }}">{{ $iva->label() }}</option>
                                    @endforeach
                                </select>
                                @error('condicion_iva') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Recargo 2do Vto (%)</label>
                                <input type="number" step="0.01" wire:model="recargo_segundo_vto" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Día 1er Vto</label>
                                <input type="number" min="1" max="28" wire:model="dia_primer_vencimiento" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Día 2do Vto</label>
                                <input type="number" min="1" max="28" wire:model="dia_segundo_vencimiento" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Logo Administración (URL/Path)</label>
                                <input type="text" wire:model="logo_administracion" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-500 outline-none" placeholder="/uploads/logo-admin.png">
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Leyenda Medios de Pago</label>
                                <textarea wire:model="texto_medios_pago" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-500 outline-none text-sm" rows="2"></textarea>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-lg font-semibold text-primary-900 border-b pb-2 mb-4">Estado y Observaciones</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nota</label>
                                <textarea wire:model="nota" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-500 outline-none text-sm" rows="3" placeholder="Notas internas del consorcio..."></textarea>
                            </div>
                            <div class="md:col-span-3">
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" wire:model="activo" class="h-4 w-4 rounded border-gray-300 text-accent-600 focus:ring-accent-500">
                                    Consorcio activo
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 border-t border-gray-100 bg-gray-50 flex justify-end gap-3 sticky bottom-0 -mx-6 -mb-6 mt-2">
                        <button type="button" wire:click="closeModal" class="px-5 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-100 transition-colors">
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            class="px-5 py-2 bg-accent-600 rounded-lg text-white font-medium hover:bg-accent-700 transition-colors shadow-sm"
                            wire:loading.attr="disabled"
                            wire:target="save"
                        >
                            <span wire:loading.remove wire:target="save">Guardar Consorcio</span>
                            <span wire:loading wire:target="save">Guardando…</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
