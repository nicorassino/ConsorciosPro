<div class="flex flex-col h-full">
    <header class="bg-white shadow-sm z-10 flex-shrink-0">
        <div class="flex items-center justify-between px-6 py-4">
            <h2 class="text-2xl font-bold text-gray-800">Unidades Funcionales</h2>
            <button type="button" wire:click="openCreateModal" class="bg-accent-600 hover:bg-accent-700 text-white px-4 py-2 rounded-lg font-medium shadow-sm transition-colors flex items-center gap-2">
                <i class="fas fa-plus"></i> Nueva Unidad
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

            <div class="glass-card rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                <div class="p-4 bg-white flex flex-wrap gap-4 items-center">
                    <div class="w-full md:w-64">
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Consorcio</label>
                        <select wire:model.live="consorcioFilter" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-500 outline-none transition-all">
                            <option value="">Todos los consorcios</option>
                            @foreach ($consorcios as $consorcio)
                                <option value="{{ $consorcio->id }}">{{ $consorcio->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Buscar Unidad</label>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por número, propietario o inquilino..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-500 outline-none transition-all">
                        </div>
                    </div>
                </div>
            </div>

            @if ($coeficienteTotal !== null)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm flex items-center gap-4">
                        <div class="h-10 w-10 rounded-lg bg-blue-50 text-blue-500 flex items-center justify-center">
                            <i class="fas fa-calculator text-lg"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-semibold">Total Coeficientes</p>
                            <p class="text-lg font-bold text-gray-800">{{ number_format($coeficienteTotal, 6, ',', '.') }}%</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="glass-card rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                                <th class="p-4 font-medium">Unidad</th>
                                <th class="p-4 font-medium">Consorcio</th>
                                <th class="p-4 font-medium">Propietario</th>
                                <th class="p-4 font-medium">Ocupación</th>
                                <th class="p-4 font-medium">Cochera</th>
                                <th class="p-4 font-medium">Coeficiente</th>
                                <th class="p-4 font-medium text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($unidades as $u)
                                <tr class="hover:bg-gray-50 transition-colors" wire:key="unidad-row-{{ $u->id }}">
                                    <td class="p-4">
                                        <div class="font-bold text-gray-800 text-lg">{{ $u->numero }}</div>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-100 text-blue-700">
                                            {{ $u->condicion_iva->label() }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-gray-600">{{ $u->consorcio->nombre }}</td>
                                    <td class="p-4">
                                        <div class="font-medium text-gray-800">{{ $u->propietario?->nombre ?? '—' }}</div>
                                        <div class="text-xs text-gray-500 truncate max-w-[200px]" title="{{ $u->propietario?->email ?? '' }}">{{ $u->propietario?->email ?? 'Sin email' }}</div>
                                    </td>
                                    <td class="p-4 text-gray-700">
                                        @if ($u->estado_ocupacion->value === 'inquilino')
                                            {{ trim(($u->inquilino?->nombre ?? '').' '.($u->inquilino?->apellido ?? '')) ?: 'Inquilino' }}
                                        @else
                                            {{ $u->estado_ocupacion->label() }}
                                        @endif
                                    </td>
                                    <td class="p-4">
                                        @if ($u->tiene_cochera && $u->nro_cochera)
                                            <span class="text-xs font-semibold px-2 py-1 bg-emerald-100 text-emerald-700 rounded-full">{{ $u->nro_cochera }}</span>
                                        @else
                                            <span class="text-sm text-gray-400 italic">Sin cochera</span>
                                        @endif
                                    </td>
                                    <td class="p-4 font-mono text-gray-700">{{ number_format((float) $u->coeficiente, 6, ',', '.') }}%</td>
                                    <td class="p-4 text-right whitespace-nowrap">
                                        <button type="button" wire:click="openEditModal({{ $u->id }})" class="text-blue-500 hover:text-blue-700 p-2" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" wire:click="delete({{ $u->id }})" wire:confirm="¿Eliminar esta unidad funcional? Esta acción puede deshacerse solo desde la base de datos (borrado lógico)." class="text-rose-500 hover:text-rose-700 p-2" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-8 text-center text-gray-500">No hay unidades cargadas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($unidades->hasPages())
                    <div class="px-4 py-3 border-t border-gray-100 bg-white">
                        {{ $unidades->links() }}
                    </div>
                @endif
            </div>
        </div>
    </main>

    @if ($showModal)
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4" wire:click.self="closeModal">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-5xl max-h-[90vh] overflow-y-auto m-4 flex flex-col">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center sticky top-0 bg-white z-10">
                    <h3 class="text-xl font-bold text-gray-800">{{ $editingId ? 'Editar Unidad Funcional' : 'Nueva Unidad Funcional' }}</h3>
                    <button type="button" wire:click="closeModal" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
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

                    <div class="bg-gray-50/50 p-6 rounded-xl border border-gray-100">
                        <h4 class="text-lg font-semibold text-primary-900 mb-4 flex items-center gap-2"><i class="fas fa-door-open text-primary-600"></i> Datos de la Unidad</h4>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Consorcio *</label>
                                <select wire:model="consorcio_id" class="w-full p-2 border border-gray-300 rounded-lg outline-none">
                                    <option value="">Seleccionar Consorcio...</option>
                                    @foreach ($consorcios as $consorcio)
                                        <option value="{{ $consorcio->id }}">{{ $consorcio->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Número Unidad *</label>
                                <input type="text" wire:model="numero" class="w-full p-2 border border-gray-300 rounded-lg outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Coeficiente (%) *</label>
                                <input type="number" step="0.000001" wire:model="coeficiente" class="w-full p-2 border border-gray-300 rounded-lg outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nro PH</label>
                                <input type="text" wire:model="nro_ph" class="w-full p-2 border border-gray-300 rounded-lg outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nro Cuenta Rentas</label>
                                <input type="text" wire:model="nro_cuenta_rentas" class="w-full p-2 border border-gray-300 rounded-lg outline-none">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nomenclatura Catastral</label>
                                <input type="text" wire:model="nomenclatura_catastral" class="w-full p-2 border border-gray-300 rounded-lg outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Estado de Ocupación</label>
                                <select wire:model="estado_ocupacion" class="w-full p-2 border border-gray-300 rounded-lg outline-none">
                                    @foreach ($estadosOcupacion as $estado)
                                        <option value="{{ $estado->value }}">{{ $estado->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700 mt-7">
                                    <input type="checkbox" wire:model="tiene_cochera" class="h-4 w-4 rounded border-gray-300 text-accent-600 focus:ring-accent-500">
                                    Tiene cochera
                                </label>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nro Cochera</label>
                                <input type="text" wire:model="nro_cochera" class="w-full p-2 border border-gray-300 rounded-lg outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nro Cupón SIRO</label>
                                <input type="text" wire:model="nro_cupon_siro" class="w-full p-2 border border-gray-300 rounded-lg outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Código Pago Electrónico</label>
                                <input type="text" wire:model="codigo_pago_electronico" class="w-full p-2 border border-gray-300 rounded-lg outline-none">
                            </div>
                        </div>
                    </div>

                    <div class="bg-amber-50/30 p-6 rounded-xl border border-amber-100">
                        <h4 class="text-lg font-semibold text-amber-800 mb-4 flex items-center gap-2"><i class="fas fa-file-invoice text-amber-600"></i> Facturación y Recibos</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Generar Recibo a nombre de *</label>
                                <select wire:model="recibos_a_nombre_de" class="w-full p-2 border border-gray-300 rounded-lg outline-none">
                                    @foreach ($recibosNombre as $recibo)
                                        <option value="{{ $recibo->value }}">{{ $recibo->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Condición de IVA *</label>
                                <select wire:model="condicion_iva" class="w-full p-2 border border-gray-300 rounded-lg outline-none">
                                    @foreach ($condicionesIva as $iva)
                                        <option value="{{ $iva->value }}">{{ $iva->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Emails Expensas Ordinarias</label>
                                <textarea wire:model="email_expensas_ordinarias" class="w-full p-2 border border-gray-300 rounded-lg outline-none text-sm" rows="2"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Emails Expensas Extraordinarias</label>
                                <textarea wire:model="email_expensas_extraordinarias" class="w-full p-2 border border-gray-300 rounded-lg outline-none text-sm" rows="2"></textarea>
                            </div>
                            <div class="md:col-span-2">
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" wire:model="activo" class="h-4 w-4 rounded border-gray-300 text-accent-600 focus:ring-accent-500">
                                    Unidad activa
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="bg-blue-50/30 p-6 rounded-xl border border-blue-100">
                        <h4 class="text-lg font-semibold text-blue-900 mb-4 flex items-center gap-2"><i class="fas fa-user-tie text-blue-600"></i> Datos del Propietario</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-2"><input type="text" wire:model="propietario_nombre" class="w-full p-2 border border-gray-300 rounded-lg outline-none" placeholder="Nombre completo *"></div>
                            <div><input type="text" wire:model="propietario_dni" class="w-full p-2 border border-gray-300 rounded-lg outline-none" placeholder="DNI / CUIT"></div>
                            <div class="md:col-span-2"><textarea wire:model="propietario_email" class="w-full p-2 border border-gray-300 rounded-lg outline-none text-sm" rows="2" placeholder="Emails"></textarea></div>
                            <div><textarea wire:model="propietario_telefono" class="w-full p-2 border border-gray-300 rounded-lg outline-none text-sm" rows="2" placeholder="Teléfonos"></textarea></div>
                            <div class="md:col-span-3"><input type="text" wire:model="propietario_direccion_postal" class="w-full p-2 border border-gray-300 rounded-lg outline-none" placeholder="Dirección postal"></div>
                        </div>
                        <div class="mt-4 border-t border-blue-200 pt-4">
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-sm font-semibold text-blue-800">Contactos Alternativos (Propietario)</p>
                                <button type="button" wire:click="addContactoPropietario" class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-md font-semibold">+ Agregar contacto</button>
                            </div>
                            @foreach ($contactos_propietario as $i => $contacto)
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-2 mb-2">
                                    <input type="text" wire:model="contactos_propietario.{{ $i }}.nombre" class="md:col-span-4 w-full p-2 border border-gray-300 rounded-lg outline-none text-sm" placeholder="Nombre">
                                    <input type="text" wire:model="contactos_propietario.{{ $i }}.telefono" class="md:col-span-3 w-full p-2 border border-gray-300 rounded-lg outline-none text-sm" placeholder="Teléfono">
                                    <input type="text" wire:model="contactos_propietario.{{ $i }}.email" class="md:col-span-4 w-full p-2 border border-gray-300 rounded-lg outline-none text-sm" placeholder="Email">
                                    <button type="button" wire:click="removeContactoPropietario({{ $i }})" class="md:col-span-1 text-rose-600 text-sm">Quitar</button>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="bg-emerald-50/30 p-6 rounded-xl border border-emerald-100">
                        <h4 class="text-lg font-semibold text-emerald-900 mb-4 flex items-center gap-2"><i class="fas fa-user text-emerald-600"></i> Datos del Inquilino (opcional)</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <input type="text" wire:model="inquilino_nombre" class="w-full p-2 border border-gray-300 rounded-lg outline-none" placeholder="Nombre">
                            <input type="text" wire:model="inquilino_apellido" class="w-full p-2 border border-gray-300 rounded-lg outline-none" placeholder="Apellido">
                            <input type="text" wire:model="inquilino_telefono" class="w-full p-2 border border-gray-300 rounded-lg outline-none" placeholder="Teléfono">
                            <input type="email" wire:model="inquilino_email" class="w-full p-2 border border-gray-300 rounded-lg outline-none md:col-span-2" placeholder="Email">
                            <input type="date" wire:model="inquilino_fecha_fin_contrato" class="w-full p-2 border border-gray-300 rounded-lg outline-none">
                            <input type="text" wire:model="inquilino_direccion_postal" class="w-full p-2 border border-gray-300 rounded-lg outline-none md:col-span-3" placeholder="Dirección postal">
                        </div>
                        <div class="mt-4 border-t border-emerald-200 pt-4">
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-sm font-semibold text-emerald-800">Contactos Alternativos (Inquilino)</p>
                                <button type="button" wire:click="addContactoInquilino" class="text-xs bg-emerald-100 text-emerald-700 px-2 py-1 rounded-md font-semibold">+ Agregar contacto</button>
                            </div>
                            @foreach ($contactos_inquilino as $i => $contacto)
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-2 mb-2">
                                    <input type="text" wire:model="contactos_inquilino.{{ $i }}.nombre" class="md:col-span-4 w-full p-2 border border-gray-300 rounded-lg outline-none text-sm" placeholder="Nombre">
                                    <input type="text" wire:model="contactos_inquilino.{{ $i }}.telefono" class="md:col-span-3 w-full p-2 border border-gray-300 rounded-lg outline-none text-sm" placeholder="Teléfono">
                                    <input type="text" wire:model="contactos_inquilino.{{ $i }}.email" class="md:col-span-4 w-full p-2 border border-gray-300 rounded-lg outline-none text-sm" placeholder="Email">
                                    <button type="button" wire:click="removeContactoInquilino({{ $i }})" class="md:col-span-1 text-rose-600 text-sm">Quitar</button>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="bg-violet-50/30 p-6 rounded-xl border border-violet-100">
                        <h4 class="text-lg font-semibold text-violet-900 mb-4 flex items-center gap-2"><i class="fas fa-city text-violet-600"></i> Datos de la Inmobiliaria (opcional)</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <input type="text" wire:model="inmobiliaria_nombre" class="w-full p-2 border border-gray-300 rounded-lg outline-none" placeholder="Nombre">
                            <input type="text" wire:model="inmobiliaria_apellido" class="w-full p-2 border border-gray-300 rounded-lg outline-none" placeholder="Apellido">
                            <input type="text" wire:model="inmobiliaria_telefono" class="w-full p-2 border border-gray-300 rounded-lg outline-none" placeholder="Teléfono">
                            <input type="email" wire:model="inmobiliaria_email" class="w-full p-2 border border-gray-300 rounded-lg outline-none md:col-span-2" placeholder="Email">
                            <input type="text" wire:model="inmobiliaria_direccion" class="w-full p-2 border border-gray-300 rounded-lg outline-none md:col-span-3" placeholder="Dirección">
                        </div>
                    </div>

                    <div class="p-6 border-t border-gray-100 bg-gray-50 flex justify-end gap-3 sticky bottom-0 -mx-6 -mb-6 mt-2">
                        <button type="button" wire:click="closeModal" class="px-5 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-100 transition-colors">Cancelar</button>
                        <button type="submit" class="px-5 py-2 bg-accent-600 rounded-lg text-white font-medium hover:bg-accent-700 transition-colors shadow-sm" wire:loading.attr="disabled" wire:target="save">
                            <span wire:loading.remove wire:target="save">Guardar Unidad</span>
                            <span wire:loading wire:target="save">Guardando…</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
