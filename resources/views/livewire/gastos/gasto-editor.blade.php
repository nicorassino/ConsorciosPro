<div class="flex flex-col h-full">
    <header class="bg-white shadow-sm z-10 flex-shrink-0">
        <div class="flex items-center justify-between px-6 py-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">{{ $isCreateMode ? 'Nuevo gasto' : 'Editar gasto' }}</h2>
                <p class="text-sm text-gray-500 mt-1">Carga de comprobantes con reparto por conceptos del presupuesto.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('gastos.index') }}" wire:navigate class="text-sm font-semibold text-gray-600 hover:text-gray-800">
                    <i class="fas fa-arrow-left mr-1"></i> Volver al listado
                </a>
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

            @if ($cargaAdjuntosBloqueada)
                <div class="rounded-lg border-2 border-red-700 bg-red-50 px-4 py-4 text-sm text-red-950">
                    <p class="font-bold text-red-900">No se pueden subir facturas ni comprobantes</p>
                    <p class="mt-2">
                        Existen archivos con más de un año en el servidor sin archivar. Andá a <a href="{{ route('gastos.index') }}#archivo-masivo" wire:navigate class="font-semibold text-red-800 underline">Gastos y facturas</a>, descargá el paquete ZIP y archivá en el servidor para liberar espacio. Después podrás adjuntar archivos otra vez.
                    </p>
                </div>
            @endif

            @error('lineItems')
                <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                    {{ $message }}
                </div>
            @enderror

            {{--
              Livewire 3: wire:model sin .live está diferido (sync en blur o al ENVIAR formulario).
              wire:click="save" NO es submit: al clicar Guardar los valores pueden seguir vacíos en el servidor.
              Usar form + wire:submit.prevent para que Livewire sincronice todos los campos antes de save().
            --}}
            <form wire:submit.prevent="save" class="space-y-6">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Consorcio *</label>
                        <select wire:model="consorcio_id" class="w-full p-2 border border-gray-300 rounded-lg outline-none">
                            <option value="">Seleccionar...</option>
                            @foreach ($consorcios as $consorcio)
                                <option value="{{ $consorcio->id }}">{{ $consorcio->nombre }}</option>
                            @endforeach
                        </select>
                        @error('consorcio_id') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Proveedor</label>
                        <div class="flex items-center gap-2">
                            <select wire:model="proveedor_id" class="w-full p-2 border border-gray-300 rounded-lg outline-none">
                                <option value="">Sin proveedor</option>
                                @foreach ($proveedores as $proveedor)
                                    <option value="{{ $proveedor->id }}">{{ $proveedor->nombre }}</option>
                                @endforeach
                            </select>
                            <button type="button" wire:click="toggleProveedorQuickForm" class="px-3 py-2 text-xs font-semibold rounded-lg border border-gray-300 hover:bg-gray-50">
                                + Proveedor
                            </button>
                        </div>
                        @error('proveedor_id') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Nro orden *</label>
                        <input type="text" wire:model="nro_orden" class="w-full p-2 border border-gray-300 rounded-lg outline-none">
                        @error('nro_orden') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Descripción *</label>
                        <input type="text" wire:model="descripcion" class="w-full p-2 border border-gray-300 rounded-lg outline-none">
                        @error('descripcion') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Importe total *</label>
                        <input type="number" step="0.01" wire:model="importe" class="w-full p-2 border border-gray-300 rounded-lg outline-none">
                        @error('importe') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Fecha factura *</label>
                        <input type="date" wire:model="fecha_factura" class="w-full p-2 border border-gray-300 rounded-lg outline-none">
                        @error('fecha_factura') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Período *</label>
                        <input type="month" wire:model="periodo" class="w-full p-2 border border-gray-300 rounded-lg outline-none">
                        @error('periodo') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Estado *</label>
                        <select wire:model.live="estado" class="w-full p-2 border border-gray-300 rounded-lg outline-none">
                            @foreach ($estados as $estadoItem)
                                <option value="{{ $estadoItem->value }}">{{ $estadoItem->label() }}</option>
                            @endforeach
                        </select>
                        @error('estado') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Fecha de pago</label>
                        <input type="date" wire:model.live="fecha_pago" @disabled($estado !== 'pagado') class="w-full p-2 border border-gray-300 rounded-lg outline-none disabled:bg-gray-100">
                        @error('fecha_pago') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Destino del ajuste por diferencia</label>
                        <select wire:model="ajuste_destino" class="w-full p-2 border border-gray-300 rounded-lg outline-none">
                            <option value="siguiente_creacion">Próximo presupuesto a crear</option>
                            <option value="ultimo_pendiente">Último presupuesto pendiente (borrador)</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Si hay diferencia entre estimado y factura real, el ajuste se enviará al destino elegido.</p>
                        @error('ajuste_destino') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                @if ($showProveedorQuickForm)
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <h4 class="font-semibold text-gray-800 mb-3">Alta rápida de proveedor</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <input type="text" wire:model="proveedor_nombre" placeholder="Nombre*" class="p-2 border border-gray-300 rounded-lg outline-none">
                            <input type="text" wire:model="proveedor_cuit" placeholder="CUIT" class="p-2 border border-gray-300 rounded-lg outline-none">
                            <input type="text" wire:model="proveedor_telefono" placeholder="Teléfono" class="p-2 border border-gray-300 rounded-lg outline-none">
                            <input type="email" wire:model="proveedor_email" placeholder="Email" class="p-2 border border-gray-300 rounded-lg outline-none md:col-span-2">
                            <input type="text" wire:model="proveedor_direccion" placeholder="Dirección" class="p-2 border border-gray-300 rounded-lg outline-none">
                        </div>
                        <div class="mt-3">
                            <button type="button" wire:click="createProveedorQuick" class="px-3 py-2 text-xs font-semibold rounded-lg bg-primary-900 text-white hover:bg-primary-800">
                                Guardar proveedor
                            </button>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Factura adjunta</label>
                        <input type="file" wire:model="factura_archivo" @disabled($cargaAdjuntosBloqueada) class="w-full p-2 border border-gray-300 rounded-lg outline-none bg-white disabled:bg-gray-100 disabled:cursor-not-allowed">
                        @if ($factura_archivo_actual)
                            <p class="text-xs text-gray-500 mt-1">Archivo actual: {{ basename($factura_archivo_actual) }}</p>
                        @endif
                        @error('factura_archivo') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Comprobante de pago</label>
                        <input type="file" wire:model="comprobante_pago" @disabled($cargaAdjuntosBloqueada || $estado !== 'pagado') class="w-full p-2 border border-gray-300 rounded-lg outline-none bg-white disabled:bg-gray-100 disabled:cursor-not-allowed">
                        @if ($comprobante_pago_actual)
                            <p class="text-xs text-gray-500 mt-1">Archivo actual: {{ basename($comprobante_pago_actual) }}</p>
                        @endif
                        @error('comprobante_pago') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Notas</label>
                    <textarea wire:model="notas" rows="3" class="w-full p-2 border border-gray-300 rounded-lg outline-none"></textarea>
                    @error('notas') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Reparto por conceptos</h3>
                    <button type="button" wire:click="addLineItem" class="px-3 py-2 text-xs font-semibold rounded-lg border border-gray-300 hover:bg-gray-50">
                        <i class="fas fa-plus mr-1"></i> Agregar línea
                    </button>
                </div>

                @if ($consorcio_id && $conceptosDisponibles->isEmpty())
                    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 mb-4">
                        No hay conceptos disponibles para el consorcio seleccionado.
                    </div>
                @endif

                <div class="space-y-3">
                    @foreach ($lineItems as $idx => $line)
                        <div wire:key="imputation-row-{{ $idx }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
                            <div class="md:col-span-7">
                                <select wire:model="lineItems.{{ $idx }}.concepto_presupuesto_id" class="w-full p-2 border border-gray-300 rounded-lg outline-none">
                                    <option value="">Seleccionar concepto...</option>
                                    @foreach ($conceptosDisponibles as $concepto)
                                        @php($estaConfirmado = $concepto->monto_factura_real !== null)
                                        <option value="{{ $concepto->id }}">
                                            {{ $concepto->nombre }} ({{ $concepto->tipo->label() }}) - {{ \Carbon\Carbon::parse($concepto->presupuesto_periodo)->format('m/Y') }} - {{ ucfirst($concepto->presupuesto_estado) }} - {{ $estaConfirmado ? 'Confirmado' : 'Estimado' }} ${{ number_format((float) ($estaConfirmado ? $concepto->monto_factura_real : $concepto->monto_total), 2, ',', '.') }}
                                        </option>
                                    @endforeach
                                </select>
                                @error("lineItems.$idx.concepto_presupuesto_id") <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="md:col-span-4">
                                <input type="number" step="0.01" wire:model="lineItems.{{ $idx }}.importe_asignado" class="w-full p-2 border border-gray-300 rounded-lg outline-none" placeholder="Importe asignado">
                                @error("lineItems.$idx.importe_asignado") <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="md:col-span-1 text-right">
                                <button type="button" wire:click="removeLineItem({{ $idx }})" class="text-rose-600 hover:text-rose-800 text-sm font-semibold">
                                    Quitar
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-5 p-3 rounded-lg bg-gray-50 border border-gray-200 flex items-center justify-between text-sm">
                    <span class="font-semibold text-gray-700">Total asignado:</span>
                    <span class="font-mono text-gray-800">${{ number_format((float) $montoAsignadoTotal, 2, ',', '.') }}</span>
                </div>
            </div>

            @if (count($ajustePreview) > 0)
                <div class="bg-white rounded-xl border border-amber-200 shadow-sm p-5 space-y-3">
                    <h3 class="text-lg font-bold text-gray-800">Preview de ajustes</h3>
                    <p class="text-sm text-gray-600">
                        Al guardar, se generarán los siguientes ajustes en el presupuesto del mes siguiente.
                    </p>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm min-w-[700px]">
                            <thead class="bg-amber-50 text-amber-900 uppercase text-xs">
                                <tr>
                                    <th class="p-2 text-left">Concepto</th>
                                    <th class="p-2 text-right">Estimado</th>
                                    <th class="p-2 text-right">Real (asignado)</th>
                                    <th class="p-2 text-right">Diferencia</th>
                                    <th class="p-2 text-left">Destino</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-amber-100">
                                @foreach ($ajustePreview as $preview)
                                    <tr>
                                        <td class="p-2 text-gray-700">{{ $preview['concepto'] }}</td>
                                        <td class="p-2 text-right font-mono text-gray-700">${{ number_format((float) $preview['estimado'], 2, ',', '.') }}</td>
                                        <td class="p-2 text-right font-mono text-gray-700">${{ number_format((float) $preview['real'], 2, ',', '.') }}</td>
                                        <td class="p-2 text-right font-mono {{ $preview['diferencia'] >= 0 ? 'text-rose-700' : 'text-emerald-700' }}">
                                            ${{ number_format((float) $preview['diferencia'], 2, ',', '.') }}
                                        </td>
                                        <td class="p-2 text-gray-600">Ajuste {{ $preview['concepto'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('gastos.index') }}" wire:navigate class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Cancelar
                </a>
                <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold bg-accent-600 text-white hover:bg-accent-700">
                    Guardar gasto
                </button>
            </div>
            </form>
        </div>
    </main>
</div>
