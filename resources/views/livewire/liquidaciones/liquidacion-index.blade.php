<div class="flex flex-col h-full">
    <header class="bg-white shadow-sm z-10 flex-shrink-0">
        <div class="flex items-center justify-between px-6 py-4">
            <h2 class="text-2xl font-bold text-gray-800">Liquidaciones</h2>
            <div class="flex items-center gap-3">
                @if ($selectedPresupuesto)
                    <button type="button" wire:click="clearSelection" class="text-sm font-semibold text-gray-600 hover:text-gray-800">
                        <i class="fas fa-arrow-left mr-1"></i> Cambiar presupuesto
                    </button>
                @endif
                <x-user-menu />
            </div>
        </div>
    </header>

    <main class="flex-1 overflow-y-auto p-6 bg-gray-50 min-h-0">
        <div class="max-w-7xl mx-auto space-y-6">
            @if (session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif
            @error('manual_distribution')
                <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                    {{ $message }}
                </div>
            @enderror

            @if (! $selectedPresupuesto)
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 grid grid-cols-1 md:grid-cols-3 gap-4">
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
                            <option value="finalizado">Finalizado</option>
                            <option value="liquidado">Liquidado</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Buscar</label>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por consorcio..." class="w-full p-2 border border-gray-300 rounded-lg outline-none">
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-200">
                            <tr>
                                <th class="p-3 text-left">Consorcio</th>
                                <th class="p-3 text-left">Período</th>
                                <th class="p-3 text-left">Estado</th>
                                <th class="p-3 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($presupuestos as $presupuesto)
                                <tr class="hover:bg-gray-50">
                                    <td class="p-3 font-semibold text-gray-800">{{ $presupuesto->consorcio->nombre }}</td>
                                    <td class="p-3 text-gray-600">{{ $presupuesto->periodo->translatedFormat('F Y') }}</td>
                                    <td class="p-3">
                                        <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $presupuesto->estado->value === 'finalizado' ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700' }}">
                                            {{ $presupuesto->estado->label() }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-right">
                                        <button type="button" wire:click="selectPresupuesto({{ $presupuesto->id }})" class="text-sm font-semibold text-blue-600 hover:text-blue-800">
                                            {{ $presupuesto->estado->value === 'finalizado' ? 'Liquidar' : 'Ver solo lectura' }}
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-8 text-center text-gray-500">No hay presupuestos finalizados o liquidados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    @if ($presupuestos->hasPages())
                        <div class="p-4 border-t border-gray-100">
                            {{ $presupuestos->links() }}
                        </div>
                    @endif
                </div>
            @else
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex items-center justify-between gap-4 flex-wrap">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">{{ $selectedPresupuesto->consorcio->nombre }} - {{ $selectedPresupuesto->periodo->translatedFormat('F Y') }}</h3>
                        <p class="text-sm text-gray-500 mt-1">
                            Presupuesto {{ $selectedPresupuesto->estado->label() }}
                            @if ($readOnly)
                                <span class="ml-2 text-xs font-semibold bg-gray-100 px-2 py-1 rounded-full text-gray-700">Solo lectura</span>
                            @endif
                        </p>
                    </div>
                    @if (! $readOnly)
                        <button type="button" wire:click="calcularLiquidacion" class="bg-accent-600 hover:bg-accent-700 text-white px-4 py-2 rounded-lg font-semibold">
                            <i class="fas fa-calculator mr-1"></i> Calcular y cerrar liquidación
                        </button>
                    @endif
                </div>

                <div class="space-y-4">
                    @foreach ($selectedPresupuesto->conceptos as $concepto)
                        @php $cfg = $conceptConfig[$concepto->id] ?? ['method' => 'coeficiente', 'solo_cocheras' => false, 'excluded' => [], 'manual' => []]; @endphp
                        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                            <div class="p-4 bg-gray-50 border-b border-gray-100 flex items-center justify-between gap-3 flex-wrap">
                                <div>
                                    <h4 class="font-bold text-gray-800">{{ $concepto->nombre }}</h4>
                                    <p class="text-xs text-gray-500">${{ number_format((float) $concepto->monto_total, 2, ',', '.') }} · {{ $concepto->tipo->label() }}</p>
                                </div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    @foreach ($metodos as $metodo)
                                        <button
                                            type="button"
                                            wire:click="setConceptMethod({{ $concepto->id }}, '{{ $metodo->value }}')"
                                            @disabled($readOnly)
                                            @class([
                                                'px-3 py-1.5 rounded-lg text-xs font-bold border',
                                                'bg-blue-100 text-blue-800 border-blue-200' => $cfg['method'] === $metodo->value && $metodo->value === 'coeficiente',
                                                'bg-purple-100 text-purple-800 border-purple-200' => $cfg['method'] === $metodo->value && $metodo->value === 'partes_iguales',
                                                'bg-amber-100 text-amber-800 border-amber-200' => $cfg['method'] === $metodo->value && $metodo->value === 'manual',
                                                'bg-white text-gray-500 border-gray-300' => $cfg['method'] !== $metodo->value,
                                                'opacity-60 cursor-not-allowed' => $readOnly,
                                            ])
                                        >
                                            {{ $metodo->label() }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            @if ($cfg['method'] === 'coeficiente')
                                <div class="px-4 py-2 border-b border-gray-100 bg-blue-50 flex items-center gap-2">
                                    <label class="inline-flex items-center gap-2 text-sm text-blue-900 font-medium">
                                        <input type="checkbox" wire:click="toggleSoloCocheras({{ $concepto->id }})" @checked($cfg['solo_cocheras']) @disabled($readOnly) class="h-4 w-4 rounded border-gray-300 text-accent-600">
                                        Solo cocheras (excluye unidades sin cochera)
                                    </label>
                                </div>
                            @endif

                            <div class="overflow-x-auto">
                                <table class="w-full text-xs">
                                    <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider">
                                        <tr>
                                            <th class="p-2 text-left">Excl.</th>
                                            <th class="p-2 text-left">Unidad</th>
                                            <th class="p-2 text-left">Prop./Inq.</th>
                                            <th class="p-2 text-right">Coef. original</th>
                                            <th class="p-2 text-right">Coef. aplicado</th>
                                            @if ($cfg['method'] === 'manual')
                                                <th class="p-2 text-center">% Manual</th>
                                            @endif
                                            <th class="p-2 text-right">Monto a pagar</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @php
                                            $excludedIds = collect($cfg['excluded']);
                                            if ($cfg['solo_cocheras']) {
                                                $excludedIds = $excludedIds->merge(
                                                    $unidades
                                                        ->filter(fn ($unit) => ! $unit->tiene_cochera)
                                                        ->pluck('id')
                                                );
                                            }
                                            $excludedIds = $excludedIds->unique()->values();
                                            $activos = $unidades->reject(fn ($unit) => $excludedIds->contains($unit->id));
                                            $sumCoefOriginal = max(0.000001, (float) $activos->sum('coeficiente'));
                                            $sumManual = max(0.000001, (float) collect($cfg['manual'])->sum());
                                            $cantidadActivos = max(1, $activos->count());
                                            $sumaVerificacion = 0.0;
                                        @endphp
                                        @foreach ($unidades as $unidad)
                                            @php
                                                $isExcluded = $excludedIds->contains($unidad->id);
                                                $coefOriginal = (float) $unidad->coeficiente;
                                                $coefAplicado = 0.0;
                                                $montoPagar = 0.0;

                                                if (! $isExcluded) {
                                                    if ($cfg['method'] === 'coeficiente') {
                                                        $coefAplicado = $coefOriginal / $sumCoefOriginal * 100;
                                                        $montoPagar = (float) $concepto->monto_total * ($coefAplicado / 100);
                                                    } elseif ($cfg['method'] === 'partes_iguales') {
                                                        $coefAplicado = 100 / $cantidadActivos;
                                                        $montoPagar = (float) $concepto->monto_total / $cantidadActivos;
                                                    } else {
                                                        $manualActual = (float) ($cfg['manual'][$unidad->id] ?? 0);
                                                        $coefAplicado = $manualActual;
                                                        $montoPagar = (float) $concepto->monto_total * ($manualActual / $sumManual);
                                                    }
                                                }
                                                $sumaVerificacion += $montoPagar;
                                            @endphp
                                            <tr @class(['bg-rose-50/40 line-through opacity-60' => $isExcluded])>
                                                <td class="p-2">
                                                    <input type="checkbox" wire:click="toggleExcludeUnit({{ $concepto->id }}, {{ $unidad->id }})" @checked($isExcluded) @disabled($readOnly) class="h-4 w-4 rounded border-gray-300 text-rose-600">
                                                </td>
                                                <td class="p-2 font-semibold text-gray-700">{{ $unidad->numero }} @if ($unidad->tiene_cochera)<span class="text-[10px] bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full ml-1">Cochera</span>@endif</td>
                                                <td class="p-2 text-gray-600">
                                                    {{ $unidad->propietario?->nombre ?? '—' }}<br>
                                                    @if ($unidad->inquilino)
                                                        <span class="text-gray-400">{{ trim(($unidad->inquilino->nombre ?? '').' '.($unidad->inquilino->apellido ?? '')) }}</span>
                                                    @else
                                                        <span class="text-gray-300 italic">Sin inquilino</span>
                                                    @endif
                                                </td>
                                                <td class="p-2 text-right font-mono text-gray-600">{{ number_format($coefOriginal, 4, ',', '.') }}%</td>
                                                <td class="p-2 text-right font-mono text-gray-700">
                                                    @if ($isExcluded)
                                                        <span class="text-rose-500 font-semibold">EXCLUIDA</span>
                                                    @else
                                                        {{ number_format($coefAplicado, 4, ',', '.') }}%
                                                    @endif
                                                </td>
                                                @if ($cfg['method'] === 'manual')
                                                    <td class="p-2 text-center">
                                                        <input type="number" min="0" max="100" step="0.01" wire:change="setManual({{ $concepto->id }}, {{ $unidad->id }}, $event.target.value)" value="{{ $cfg['manual'][$unidad->id] ?? 0 }}" @disabled($readOnly || $isExcluded) class="w-20 p-1 border border-gray-300 rounded-lg text-center">
                                                    </td>
                                                @endif
                                                <td class="p-2 text-right font-mono font-semibold text-gray-800">
                                                    @if ($isExcluded)
                                                        $0,00
                                                    @else
                                                        ${{ number_format($montoPagar, 2, ',', '.') }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    @php
                                        $diferencia = abs($sumaVerificacion - (float) $concepto->monto_total);
                                        $verificacionOk = $diferencia < 0.5;
                                    @endphp
                                    <tfoot class="border-t border-gray-200 bg-gray-50">
                                        <tr>
                                            <td class="p-2 text-right font-bold text-gray-500" colspan="{{ $cfg['method'] === 'manual' ? 6 : 5 }}">
                                                Total participantes / verificación:
                                            </td>
                                            <td class="p-2 text-right font-mono font-bold {{ $verificacionOk ? 'text-emerald-600' : 'text-rose-600' }}">
                                                ${{ number_format($sumaVerificacion, 2, ',', '.') }} {{ $verificacionOk ? '✓' : '⚠' }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($showResult)
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-white rounded-xl p-4 border-t-4 border-emerald-500 shadow-sm">
                            <p class="text-xs font-semibold text-gray-500 uppercase">Total Ordinario</p>
                            <p class="text-2xl font-bold text-gray-800 mt-1">${{ number_format($totals['total_ordinario'], 2, ',', '.') }}</p>
                        </div>
                        <div class="bg-white rounded-xl p-4 border-t-4 border-amber-500 shadow-sm">
                            <p class="text-xs font-semibold text-gray-500 uppercase">Total Extraordinario</p>
                            <p class="text-2xl font-bold text-gray-800 mt-1">${{ number_format($totals['total_extraordinario'], 2, ',', '.') }}</p>
                        </div>
                        <div class="bg-primary-900 rounded-xl p-4 shadow-md text-white">
                            <p class="text-xs font-semibold text-white/70 uppercase">Total General</p>
                            <p class="text-2xl font-bold mt-1">${{ number_format($totals['total_general'], 2, ',', '.') }}</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-200">
                                <tr>
                                    <th class="p-3 text-left">Unidad</th>
                                    <th class="p-3 text-right">Ordinario</th>
                                    <th class="p-3 text-right">Extraordinario</th>
                                    <th class="p-3 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($unidades as $unidad)
                                    @php $res = $resultByUnidad[$unidad->id] ?? ['ordinario' => 0, 'extraordinario' => 0, 'total' => 0]; @endphp
                                    <tr>
                                        <td class="p-3 font-semibold text-gray-800">Unidad {{ $unidad->numero }}</td>
                                        <td class="p-3 text-right font-mono">${{ number_format($res['ordinario'], 2, ',', '.') }}</td>
                                        <td class="p-3 text-right font-mono">${{ number_format($res['extraordinario'], 2, ',', '.') }}</td>
                                        <td class="p-3 text-right font-mono font-bold">${{ number_format($res['total'], 2, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @endif
        </div>
    </main>
</div>
