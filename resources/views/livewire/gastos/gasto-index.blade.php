<div class="flex flex-col h-full">
    <header class="bg-white shadow-sm z-10 flex-shrink-0">
        <div class="flex items-center justify-between px-6 py-4">
            <h2 class="text-2xl font-bold text-gray-800">Gastos y Facturas</h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('gastos.create') }}" wire:navigate class="bg-accent-600 hover:bg-accent-700 text-white px-4 py-2 rounded-lg font-semibold text-sm">
                    <i class="fas fa-plus mr-1"></i> Nuevo gasto
                </a>
                <x-user-menu />
            </div>
        </div>
    </header>

    <main class="flex-1 overflow-y-auto p-6 bg-gray-50 min-h-0">
        <div class="max-w-7xl mx-auto space-y-6">
            @if ($adjuntosBloqueadosPorRetencion)
                <div class="rounded-lg border-2 border-red-700 bg-red-50 px-4 py-4 text-sm text-red-950 shadow-sm">
                    <p class="font-bold text-red-900">
                        Carga de archivos suspendida en todo el módulo
                    </p>
                    <p class="mt-2">
                        Hay facturas o comprobantes que superaron el año de almacenamiento online sin archivar. Subí nuevos archivos solo después de descargar el paquete y archivar en el servidor.
                    </p>
                    <a href="#archivo-masivo" class="mt-2 inline-block text-sm font-semibold text-red-800 underline">
                        Ir a archivado masivo
                    </a>
                </div>
            @endif

            @if (session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            @if ($mostrarAvisoAnualArchivo)
                <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <span>
                        El archivado masivo de archivos en servidor es un procedimiento habitual <strong>una vez al año</strong>: primero descargá el paquete ZIP completo y guardalo en tu soporte local; recién después el sistema elimina las copias online.
                    </span>
                    <button type="button" wire:click="ackAvisoAnualArchivo" class="shrink-0 px-3 py-2 rounded-lg bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700">
                        Entendido
                    </button>
                </div>
            @endif

            <div id="archivo-masivo" class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 space-y-3 scroll-mt-6">
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Archivado masivo (fecha de factura)</h3>
                    <p class="text-xs text-gray-600 mt-1">
                        Se incluyen todos los gastos con archivos aún <strong>online</strong> cuya <strong>fecha de factura</strong> sea <strong>anterior</strong> al mes seleccionado. Se genera un ZIP ordenado por consorcio y mes; luego se eliminan las copias del servidor (irreversible).
                    </p>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-end gap-3 flex-wrap">
                    <div class="min-w-[200px]">
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Archivar todo lo anterior a</label>
                        <input type="month" wire:model="archivoCorte" class="w-full p-2 border border-gray-300 rounded-lg outline-none">
                    </div>
                    <button
                        type="button"
                        wire:click="descargarPaqueteYArchivarEnServidor"
                        wire:confirm="¿Confirmás el archivado masivo? Se descargará un ZIP con facturas y comprobantes incluidos, y después se borrarán esas copias del servidor. Debés tener el archivo guardado localmente; esta acción no se puede deshacer."
                        class="px-4 py-2 rounded-lg bg-gray-900 text-white text-sm font-semibold hover:bg-gray-800"
                    >
                        Descargar paquete y archivar en servidor
                    </button>
                </div>
            </div>

            @if ($gastosUrgentesRetencion->isNotEmpty())
                <div class="rounded-lg border-2 border-red-600 bg-red-50 px-4 py-4 text-sm text-red-950 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 shadow-sm">
                    <div>
                        <p class="font-bold text-red-900">
                            Quedan 30 días o menos para cumplir un año desde la fecha de factura (archivos online)
                        </p>
                        <p class="mt-1">
                            Hay <strong>{{ $gastosUrgentesRetencion->count() }}</strong> gasto(s) en riesgo. Descargá el paquete y usá <strong>Descargar paquete y archivar en servidor</strong> arriba cuando corresponda. Podés seguir trabajando con normalidad en este módulo.
                        </p>
                    </div>
                    <button type="button" wire:click="descargarTodosProximosVencer" class="shrink-0 px-3 py-2 rounded-lg bg-red-700 text-white text-xs font-semibold hover:bg-red-800">
                        Descargar ZIP (urgentes)
                    </button>
                </div>
            @endif

            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Consorcio</label>
                    <select wire:model.live="consorcioFilter" class="w-full p-2 border border-gray-300 rounded-lg outline-none">
                        <option value="">Todos</option>
                        @foreach ($consorcios as $consorcio)
                            <option value="{{ $consorcio->id }}">{{ $consorcio->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Estado</label>
                    <select wire:model.live="estadoFilter" class="w-full p-2 border border-gray-300 rounded-lg outline-none">
                        <option value="">Todos</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="pagado">Pagado</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Período</label>
                    <input type="month" wire:model.live="periodoFilter" class="w-full p-2 border border-gray-300 rounded-lg outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Archivo</label>
                    <select wire:model.live="archivoFilter" class="w-full p-2 border border-gray-300 rounded-lg outline-none">
                        <option value="">Todos</option>
                        <option value="online">Online</option>
                        <option value="archivado">Archivado</option>
                        <option value="sin_archivo">Sin archivo</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Buscar</label>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Descripción, orden, proveedor..." class="w-full p-2 border border-gray-300 rounded-lg outline-none">
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                <table class="w-full min-w-[1100px] text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-200">
                        <tr>
                            <th class="p-3 text-left">Nro orden</th>
                            <th class="p-3 text-left">Consorcio</th>
                            <th class="p-3 text-left">Proveedor</th>
                            <th class="p-3 text-left">Período</th>
                            <th class="p-3 text-right">Importe</th>
                            <th class="p-3 text-left">Estado</th>
                            <th class="p-3 text-left">Ajuste generado</th>
                            <th class="p-3 text-left">Archivo</th>
                            <th class="p-3 text-center">Editar</th>
                            <th class="p-3 text-center">Eliminar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($gastos as $gasto)
                            @php($ajuste = $ajustesMesSiguiente[$gasto->id] ?? ['has' => false, 'amount' => 0.0])
                            @php($mesAjuste = $gasto->periodo->copy()->startOfMonth()->addMonth()->format('m/Y'))
                            <tr class="hover:bg-gray-50">
                                <td class="p-3 font-semibold text-gray-800">{{ $gasto->nro_orden }}</td>
                                <td class="p-3 text-gray-700">{{ $gasto->consorcio->nombre }}</td>
                                <td class="p-3 text-gray-600">{{ $gasto->proveedor?->nombre ?? 'Sin proveedor' }}</td>
                                <td class="p-3 text-gray-600">{{ $gasto->periodo->format('m/Y') }}</td>
                                <td class="p-3 text-right font-mono text-gray-700">${{ number_format((float) $gasto->importe, 2, ',', '.') }}</td>
                                <td class="p-3">
                                    <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $gasto->estado->value === 'pagado' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                        {{ $gasto->estado->label() }}
                                    </span>
                                </td>
                                <td class="p-3">
                                    @if ($ajuste['has'])
                                        <div class="inline-flex flex-col gap-1">
                                            <span class="text-xs font-semibold px-2 py-1 rounded-full bg-emerald-100 text-emerald-700 w-fit">
                                                Sí
                                            </span>
                                            <span class="text-xs font-mono text-emerald-700">
                                                ${{ number_format((float) $ajuste['amount'], 2, ',', '.') }}
                                            </span>
                                            <span class="text-xs text-gray-500">
                                                Aparece en {{ $mesAjuste }}
                                            </span>
                                        </div>
                                    @else
                                        <div class="inline-flex flex-col gap-1">
                                            <span class="text-xs font-semibold px-2 py-1 rounded-full bg-gray-100 text-gray-600 w-fit">
                                                No
                                            </span>
                                            <span class="text-xs font-mono text-gray-500">$0,00</span>
                                            <span class="text-xs text-gray-500">
                                                Mes {{ $mesAjuste }}
                                            </span>
                                        </div>
                                    @endif
                                </td>
                                <td class="p-3">
                                    @if ($gasto->archivo_disponible_online && ($gasto->factura_archivo || $gasto->comprobante_pago))
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="text-xs font-semibold px-2 py-1 rounded-full bg-emerald-100 text-emerald-700">Online</span>
                                            @if ($gasto->factura_archivo)
                                                <button
                                                    type="button"
                                                    wire:click="downloadArchivo({{ $gasto->id }}, 'factura')"
                                                    class="text-sm font-semibold text-indigo-600 hover:text-indigo-800"
                                                    title="Descargar factura"
                                                    aria-label="Descargar factura"
                                                >
                                                    <i class="fas fa-file-invoice"></i>
                                                </button>
                                            @endif
                                            @if ($gasto->comprobante_pago)
                                                <button
                                                    type="button"
                                                    wire:click="downloadArchivo({{ $gasto->id }}, 'comprobante')"
                                                    class="text-sm font-semibold text-emerald-600 hover:text-emerald-800"
                                                    title="Descargar comprobante de pago"
                                                    aria-label="Descargar comprobante de pago"
                                                >
                                                    <i class="fas fa-receipt"></i>
                                                </button>
                                            @endif
                                        </div>
                                    @elseif (! $gasto->archivo_disponible_online && $gasto->fecha_archivado_local)
                                        <span class="text-xs font-semibold px-2 py-1 rounded-full bg-rose-100 text-rose-700">
                                            Archivado el {{ $gasto->fecha_archivado_local->format('d/m/Y') }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-500">Sin archivo</span>
                                    @endif
                                </td>
                                <td class="p-3 text-center">
                                    <a
                                        href="{{ route('gastos.show', $gasto) }}"
                                        wire:navigate
                                        class="text-sm font-semibold text-blue-600 hover:text-blue-800"
                                        title="Editar gasto"
                                        aria-label="Editar gasto"
                                    >
                                        <i class="fas fa-pen-to-square"></i>
                                    </a>
                                </td>
                                <td class="p-3 text-center">
                                    <button
                                        type="button"
                                        wire:click="deleteGasto({{ $gasto->id }})"
                                        wire:confirm="¿Seguro que querés eliminar este gasto?"
                                        class="text-sm font-semibold text-rose-600 hover:text-rose-800"
                                        title="Eliminar gasto"
                                        aria-label="Eliminar gasto"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="p-8 text-center text-gray-500">No hay gastos registrados para los filtros aplicados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
                @if ($gastos->hasPages())
                    <div class="p-4 border-t border-gray-100">
                        {{ $gastos->links() }}
                    </div>
                @endif
            </div>
        </div>
    </main>
</div>
