<div class="flex flex-col h-full">
    <header class="bg-white shadow-sm z-10 flex-shrink-0">
        <div class="px-6 py-4 flex items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Fase 7: Informes y Conciliación</h2>
                <p class="text-sm text-gray-500 mt-1">Conciliación bancaria, reportes económicos, deuda y estadísticas de gestión.</p>
            </div>
            <x-user-menu />
        </div>
    </header>

    <main class="flex-1 overflow-y-auto p-6 bg-gray-50 min-h-0">
        <div class="max-w-7xl mx-auto space-y-6">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Consorcio</label>
                    <select wire:model.live="consorcioFilter" class="w-full p-2 border border-gray-300 rounded-lg outline-none">
                        @foreach ($consorcios as $consorcio)
                            <option value="{{ $consorcio->id }}">{{ $consorcio->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Período</label>
                    <input type="month" wire:model.live="periodFilter" class="w-full p-2 border border-gray-300 rounded-lg outline-none">
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-3">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap gap-2">
                    @foreach ([
                        'conciliacion' => 'Conciliación',
                        'economico' => 'Informe económico',
                        'deuda' => 'Deuda',
                        'estadisticas' => 'Estadísticas'
                    ] as $key => $label)
                        <button
                            type="button"
                            wire:click="setTab('{{ $key }}')"
                            @class([
                                'px-3 py-2 rounded-lg text-sm font-semibold',
                                'bg-primary-900 text-white' => $tab === $key,
                                'bg-gray-100 text-gray-700 hover:bg-gray-200' => $tab !== $key,
                            ])
                        >
                            {{ $label }}
                        </button>
                    @endforeach
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button
                            type="button"
                            wire:click="exportCsv"
                            class="px-3 py-2 rounded-lg text-sm font-semibold bg-emerald-100 text-emerald-700 hover:bg-emerald-200"
                        >
                            <i class="fas fa-file-csv mr-1"></i> Exportar CSV
                        </button>
                        <button
                            type="button"
                            wire:click="exportResumenPdf"
                            class="px-3 py-2 rounded-lg text-sm font-semibold bg-rose-100 text-rose-700 hover:bg-rose-200"
                        >
                            <i class="fas fa-file-pdf mr-1"></i> Descargar PDF
                        </button>
                    </div>
                </div>
            </div>

            @if (! $data)
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 text-gray-500">No hay consorcios para reportar.</div>
            @elseif ($tab === 'conciliacion')
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-white rounded-xl p-4 border-t-4 border-sky-500 shadow-sm">
                        <p class="text-xs text-gray-500 uppercase font-semibold">Saldo inicial</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">${{ number_format($data['conciliacion']['saldo_inicial'], 2, ',', '.') }}</p>
                    </div>
                    <div class="bg-white rounded-xl p-4 border-t-4 border-emerald-500 shadow-sm">
                        <p class="text-xs text-gray-500 uppercase font-semibold">Ingresos</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">${{ number_format($data['conciliacion']['ingresos'], 2, ',', '.') }}</p>
                    </div>
                    <div class="bg-white rounded-xl p-4 border-t-4 border-rose-500 shadow-sm">
                        <p class="text-xs text-gray-500 uppercase font-semibold">Egresos</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">${{ number_format($data['conciliacion']['egresos'], 2, ',', '.') }}</p>
                    </div>
                    <div class="bg-primary-900 rounded-xl p-4 shadow-md text-white">
                        <p class="text-xs text-white/70 uppercase font-semibold">Saldo disponible</p>
                        <p class="text-2xl font-bold mt-1">${{ number_format($data['conciliacion']['saldo_disponible'], 2, ',', '.') }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                        <h3 class="font-semibold text-gray-800">Contraste financiero</h3>
                        <div class="mt-3 space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span>Saldo teórico del período</span>
                                <strong>${{ number_format($data['conciliacion']['saldo_teorico'], 2, ',', '.') }}</strong>
                            </div>
                            <div class="flex justify-between">
                                <span>Obligaciones pendientes</span>
                                <strong>${{ number_format($data['conciliacion']['obligaciones_pendientes'], 2, ',', '.') }}</strong>
                            </div>
                            <div class="flex justify-between pt-2 border-t border-gray-200">
                                <span>Holgura (disponible - obligaciones)</span>
                                <strong class="{{ $data['conciliacion']['holgura'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                    ${{ number_format($data['conciliacion']['holgura'], 2, ',', '.') }}
                                </strong>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                        <h3 class="font-semibold text-gray-800">Período analizado</h3>
                        <p class="text-sm text-gray-600 mt-2">
                            Desde {{ \Carbon\Carbon::parse($data['conciliacion']['periodo_desde'])->format('d/m/Y') }}
                            hasta {{ \Carbon\Carbon::parse($data['conciliacion']['periodo_hasta'])->format('d/m/Y') }}.
                        </p>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-200">
                            <tr>
                                <th class="p-3 text-left">Fecha</th>
                                <th class="p-3 text-left">Tipo</th>
                                <th class="p-3 text-left">Descripción</th>
                                <th class="p-3 text-right">Monto</th>
                                <th class="p-3 text-right">Saldo resultante</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($data['conciliacion']['movimientos'] as $mov)
                                <tr>
                                    <td class="p-3">{{ $mov->fecha->format('d/m/Y') }}</td>
                                    <td class="p-3">
                                        <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $mov->tipo === 'ingreso' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                            {{ ucfirst($mov->tipo) }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-gray-700">{{ $mov->descripcion ?: 'Sin descripción' }}</td>
                                    <td class="p-3 text-right font-mono">${{ number_format($mov->monto, 2, ',', '.') }}</td>
                                    <td class="p-3 text-right font-mono">${{ number_format($mov->saldo_resultante, 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="p-6 text-center text-gray-500">Sin movimientos para el período seleccionado.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @elseif ($tab === 'economico')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-white rounded-xl p-4 border-t-4 border-indigo-500 shadow-sm">
                        <p class="text-xs text-gray-500 uppercase font-semibold">Egresos devengados</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">${{ number_format($data['informe']['totales']['devengado'], 2, ',', '.') }}</p>
                    </div>
                    <div class="bg-white rounded-xl p-4 border-t-4 border-emerald-500 shadow-sm">
                        <p class="text-xs text-gray-500 uppercase font-semibold">Egresos efectivamente pagados</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">${{ number_format($data['informe']['totales']['percibido'], 2, ',', '.') }}</p>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-200">
                            <tr>
                                <th class="p-3 text-left">Rubro</th>
                                <th class="p-3 text-left">Concepto</th>
                                <th class="p-3 text-right">Devengado</th>
                                <th class="p-3 text-right">Pagado mes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($data['informe']['rows'] as $row)
                                <tr>
                                    <td class="p-3">{{ ucfirst($row['rubro']) }}</td>
                                    <td class="p-3">{{ $row['concepto'] }}</td>
                                    <td class="p-3 text-right font-mono">${{ number_format($row['devengado'], 2, ',', '.') }}</td>
                                    <td class="p-3 text-right font-mono">${{ number_format($row['percibido'], 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="p-6 text-center text-gray-500">No hay egresos imputados para el período.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @elseif ($tab === 'deuda')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-100">
                            <h3 class="font-semibold text-gray-800">Reporte de deudores</h3>
                        </div>
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-200">
                                <tr>
                                    <th class="p-3 text-left">Unidad</th>
                                    <th class="p-3 text-right">Liquidado</th>
                                    <th class="p-3 text-right">Cobrado</th>
                                    <th class="p-3 text-right">Saldo</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($data['deuda']['deudores'] as $deudor)
                                    <tr>
                                        <td class="p-3">UF {{ $deudor['unidad'] }}</td>
                                        <td class="p-3 text-right font-mono">${{ number_format($deudor['liquidado'], 2, ',', '.') }}</td>
                                        <td class="p-3 text-right font-mono">${{ number_format($deudor['cobrado'], 2, ',', '.') }}</td>
                                        <td class="p-3 text-right font-mono font-bold text-rose-600">${{ number_format($deudor['saldo'], 2, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="p-6 text-center text-gray-500">Sin deuda pendiente para el período.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-100">
                            <h3 class="font-semibold text-gray-800">Compromisos por proveedor</h3>
                        </div>
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-200">
                                <tr>
                                    <th class="p-3 text-left">Proveedor</th>
                                    <th class="p-3 text-right">Comprobantes</th>
                                    <th class="p-3 text-right">Monto pendiente</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($data['deuda']['proveedores'] as $prov)
                                    <tr>
                                        <td class="p-3">{{ $prov['proveedor'] }}</td>
                                        <td class="p-3 text-right">{{ $prov['compromisos'] }}</td>
                                        <td class="p-3 text-right font-mono font-semibold">${{ number_format($prov['monto_pendiente'], 2, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="p-6 text-center text-gray-500">Sin proveedores con compromisos pendientes.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div class="bg-white rounded-xl p-4 border-t-4 border-emerald-500 shadow-sm">
                        <p class="text-xs text-gray-500 uppercase font-semibold">Capital cobrado</p>
                        <p class="text-xl font-bold text-gray-800 mt-1">${{ number_format($data['stats']['capital_cobrado'], 2, ',', '.') }}</p>
                    </div>
                    <div class="bg-white rounded-xl p-4 border-t-4 border-amber-500 shadow-sm">
                        <p class="text-xs text-gray-500 uppercase font-semibold">Interés cobrado</p>
                        <p class="text-xl font-bold text-gray-800 mt-1">${{ number_format($data['stats']['interes_cobrado'], 2, ',', '.') }}</p>
                    </div>
                    <div class="bg-white rounded-xl p-4 border-t-4 border-sky-500 shadow-sm">
                        <p class="text-xs text-gray-500 uppercase font-semibold">Total cobrado</p>
                        <p class="text-xl font-bold text-gray-800 mt-1">${{ number_format($data['stats']['total_cobrado'], 2, ',', '.') }}</p>
                    </div>
                    <div class="bg-white rounded-xl p-4 border-t-4 border-purple-500 shadow-sm">
                        <p class="text-xs text-gray-500 uppercase font-semibold">Pagos registrados</p>
                        <p class="text-xl font-bold text-gray-800 mt-1">{{ $data['stats']['pagos_registrados'] }}</p>
                    </div>
                    <div class="bg-primary-900 rounded-xl p-4 shadow-md text-white">
                        <p class="text-xs text-white/70 uppercase font-semibold">Tasa de cobrabilidad</p>
                        <p class="text-xl font-bold mt-1">{{ number_format($data['stats']['cobrabilidad'], 2, ',', '.') }}%</p>
                    </div>
                </div>
            @endif
        </div>
    </main>
</div>
