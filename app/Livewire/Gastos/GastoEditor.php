<?php

namespace App\Livewire\Gastos;

use App\Enums\EstadoGasto;
use App\Http\Requests\StoreGastoRequest;
use App\Http\Requests\UpdateGastoRequest;
use App\Models\ConceptoPresupuesto;
use App\Models\Consorcio;
use App\Models\Gasto;
use App\Models\Proveedor;
use App\Services\GastoOnlineRetention;
use App\Services\GastoService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

class GastoEditor extends Component
{
    use WithFileUploads;

    public ?Gasto $gasto = null;

    public bool $isCreateMode = false;

    public ?int $consorcio_id = null;

    public ?int $proveedor_id = null;

    public string $nro_orden = '';

    public string $descripcion = '';

    public ?string $importe = null;

    public string $fecha_factura = '';

    public string $periodo = '';

    public string $estado = '';

    public ?string $fecha_pago = null;

    public string $notas = '';

    public $factura_archivo = null;

    public $comprobante_pago = null;

    public ?string $factura_archivo_actual = null;

    public ?string $comprobante_pago_actual = null;

    public string $ajuste_destino = 'siguiente_creacion';

    /** @var array<int, array{concepto_presupuesto_id:string, importe_asignado:string}> */
    public array $lineItems = [];

    public bool $showProveedorQuickForm = false;

    public string $proveedor_nombre = '';

    public string $proveedor_cuit = '';

    public string $proveedor_telefono = '';

    public string $proveedor_email = '';

    public string $proveedor_direccion = '';

    public function mount(?Gasto $gasto = null): void
    {
        $this->estado = EstadoGasto::Pendiente->value;

        if ($gasto) {
            $this->isCreateMode = false;
            $this->gasto = $gasto->load(['conceptosPresupuesto', 'consorcio', 'proveedor']);
            $this->consorcio_id = $gasto->consorcio_id;
            $this->proveedor_id = $gasto->proveedor_id;
            $this->nro_orden = $gasto->nro_orden;
            $this->descripcion = $gasto->descripcion;
            $this->importe = number_format((float) $gasto->importe, 2, '.', '');
            $this->fecha_factura = optional($gasto->fecha_factura)->format('Y-m-d') ?? '';
            $this->periodo = optional($gasto->periodo)->format('Y-m') ?? '';
            $this->estado = $gasto->estado->value;
            $this->fecha_pago = optional($gasto->fecha_pago)->format('Y-m-d');
            $this->notas = $gasto->notas ?? '';
            $this->factura_archivo_actual = $gasto->factura_archivo;
            $this->comprobante_pago_actual = $gasto->comprobante_pago;
            $this->lineItems = $gasto->conceptosPresupuesto
                ->map(fn (ConceptoPresupuesto $concepto): array => [
                    'concepto_presupuesto_id' => (string) $concepto->id,
                    'importe_asignado' => number_format((float) $concepto->pivot->importe_asignado, 2, '.', ''),
                ])
                ->values()
                ->all();

            return;
        }

        $this->isCreateMode = true;
        $this->fecha_factura = now()->toDateString();
        $this->periodo = now()->format('Y-m');
        $this->nro_orden = 'ORD-'.now()->format('Ymd-His');
        $this->lineItems = [['concepto_presupuesto_id' => '', 'importe_asignado' => '0.00']];
    }

    public function updatedConsorcioId(): void
    {
        foreach ($this->lineItems as $idx => $line) {
            $this->lineItems[$idx]['concepto_presupuesto_id'] = '';
        }
    }

    public function updatedPeriodo(): void
    {
        foreach ($this->lineItems as $idx => $line) {
            $this->lineItems[$idx]['concepto_presupuesto_id'] = '';
        }
    }

    public function addLineItem(): void
    {
        $this->lineItems[] = ['concepto_presupuesto_id' => '', 'importe_asignado' => '0.00'];
    }

    public function removeLineItem(int $index): void
    {
        if (count($this->lineItems) <= 1) {
            return;
        }

        unset($this->lineItems[$index]);
        $this->lineItems = array_values($this->lineItems);
    }

    public function toggleProveedorQuickForm(): void
    {
        $this->showProveedorQuickForm = ! $this->showProveedorQuickForm;
    }

    public function createProveedorQuick(): void
    {
        $this->validate([
            'proveedor_nombre' => ['required', 'string', 'max:191'],
            'proveedor_cuit' => ['nullable', 'string', 'max:20'],
            'proveedor_telefono' => ['nullable', 'string', 'max:191'],
            'proveedor_email' => ['nullable', 'email', 'max:191'],
            'proveedor_direccion' => ['nullable', 'string', 'max:191'],
        ]);

        $proveedor = Proveedor::query()->create([
            'nombre' => $this->proveedor_nombre,
            'cuit' => $this->proveedor_cuit ?: null,
            'telefono' => $this->proveedor_telefono ?: null,
            'email' => $this->proveedor_email ?: null,
            'direccion' => $this->proveedor_direccion ?: null,
            'activo' => true,
        ]);

        $this->proveedor_id = $proveedor->id;
        $this->showProveedorQuickForm = false;
        $this->proveedor_nombre = '';
        $this->proveedor_cuit = '';
        $this->proveedor_telefono = '';
        $this->proveedor_email = '';
        $this->proveedor_direccion = '';
    }

    public function save(): void
    {
        $this->resetValidation();

        if (GastoOnlineRetention::anyPastDeadlineBlocking()) {
            if ($this->factura_archivo !== null && $this->factura_archivo !== '') {
                $this->addError(
                    'factura_archivo',
                    'La carga de archivos está suspendida: hay facturas con más de un año online. En Gastos y facturas usá «Descargar paquete y archivar en servidor» hasta liberar el servidor.'
                );

                return;
            }
            if ($this->comprobante_pago !== null && $this->comprobante_pago !== '') {
                $this->addError(
                    'comprobante_pago',
                    'La carga de archivos está suspendida: hay facturas con más de un año online. En Gastos y facturas usá «Descargar paquete y archivar en servidor» hasta liberar el servidor.'
                );

                return;
            }
        }

        $service = app(GastoService::class);

        $normalizedConsorcioId = $this->normalizeNullableForeignKey($this->consorcio_id);
        $normalizedProveedorId = $this->normalizeNullableForeignKey($this->proveedor_id);
        $normalizedNroOrden = trim((string) $this->extractSynthValue($this->nro_orden));
        $normalizedDescripcion = trim((string) $this->extractSynthValue($this->descripcion));
        $normalizedImporte = $this->normalizeImporteScalar($this->importe);
        $normalizedFechaFactura = (string) ($this->extractSynthValue($this->fecha_factura) ?? '');
        $normalizedPeriodo = (string) ($this->extractSynthValue($this->periodo) ?? '');
        $normalizedEstado = (string) ($this->extractSynthValue($this->estado) ?? '');
        $normalizedFechaPago = $this->normalizeNullableDate($this->fecha_pago);
        $normalizedNotas = trim((string) ($this->extractSynthValue($this->notas) ?? ''));
        $normalizedAjusteDestino = (string) ($this->extractSynthValue($this->ajuste_destino) ?? 'siguiente_creacion');

        // Misma lista que valida el Validator: si solo validamos filas “útiles” pero el foreach
        // sigue usando índices viejos, los @error(lineItems.{i}.*) aparecen en la fila equivocada
        // (típico: primera fila vacía + datos en la segunda → errores en la primera).
        $this->lineItems = array_values($this->sanitizeLineItems($this->lineItems));
        if ($this->lineItems === []) {
            $this->lineItems = [['concepto_presupuesto_id' => '', 'importe_asignado' => '0.00']];
        }

        $this->consorcio_id = $normalizedConsorcioId;
        $this->proveedor_id = $normalizedProveedorId;
        $this->nro_orden = $normalizedNroOrden;
        $this->descripcion = $normalizedDescripcion;
        $this->importe = $normalizedImporte;
        $this->fecha_factura = $normalizedFechaFactura;
        $this->periodo = $normalizedPeriodo;
        $this->estado = $normalizedEstado;
        $this->fecha_pago = $normalizedFechaPago;
        $this->notas = $normalizedNotas;
        $this->ajuste_destino = $normalizedAjusteDestino;

        $request = $this->isCreateMode ? new StoreGastoRequest : new UpdateGastoRequest;

        $payload = [
            'consorcio_id' => $normalizedConsorcioId,
            'proveedor_id' => $normalizedProveedorId,
            'nro_orden' => $normalizedNroOrden,
            'descripcion' => $normalizedDescripcion,
            'importe' => $normalizedImporte,
            'fecha_factura' => $normalizedFechaFactura === '' ? null : $normalizedFechaFactura,
            'periodo' => $normalizedPeriodo === '' ? null : $normalizedPeriodo,
            'estado' => $normalizedEstado === '' ? null : $normalizedEstado,
            'fecha_pago' => $normalizedFechaPago === '' ? null : $normalizedFechaPago,
            'notas' => $normalizedNotas !== '' ? $normalizedNotas : null,
            'ajuste_destino' => $normalizedAjusteDestino === '' ? null : $normalizedAjusteDestino,
            'lineItems' => $this->lineItems,
            'factura_archivo' => $this->factura_archivo,
            'comprobante_pago' => $this->comprobante_pago,
        ];

        $validator = Validator::make(
            $payload,
            $request->rules(),
            $request->messages(),
            $request->attributes()
        );

        if ($validator->fails()) {
            $this->setErrorBag($validator->errors());

            return;
        }

        $validated = $validator->validated();

        $facturaPath = $this->factura_archivo_actual;
        $facturaNombreSistema = $this->gasto?->factura_nombre_sistema;
        if ($this->factura_archivo) {
            $facturaNombreSistema = $service->buildSystemFilename(
                (int) $validated['consorcio_id'],
                (string) $validated['periodo'],
                (array) $validated['lineItems'],
                $this->factura_archivo->getClientOriginalExtension()
            );
            $facturaPath = $this->factura_archivo->storeAs(
                'gastos/facturas',
                $facturaNombreSistema
            );
        }

        $comprobantePath = $this->comprobante_pago_actual;
        if ($this->comprobante_pago) {
            $comprobanteNombreSistema = pathinfo(
                $service->buildSystemFilename(
                    (int) $validated['consorcio_id'],
                    (string) $validated['periodo'],
                    (array) $validated['lineItems'],
                    $this->comprobante_pago->getClientOriginalExtension()
                ),
                PATHINFO_FILENAME
            ).'_comprobante.'.strtolower($this->comprobante_pago->getClientOriginalExtension());

            $comprobantePath = $this->comprobante_pago->storeAs(
                'gastos/comprobantes',
                $comprobanteNombreSistema
            );
        }

        if ($this->estado === EstadoGasto::Pagado->value && ! $this->fecha_pago) {
            $this->fecha_pago = now()->toDateString();
        }

        if ($this->estado === EstadoGasto::Pendiente->value) {
            $this->fecha_pago = null;
            $comprobantePath = null;
        }

        $hasOnlineFiles = $facturaPath !== null || $comprobantePath !== null;

        try {
            $gasto = $service->save([
                'consorcio_id' => $validated['consorcio_id'],
                'proveedor_id' => $validated['proveedor_id'] ?? null,
                'nro_orden' => $validated['nro_orden'],
                'descripcion' => $validated['descripcion'],
                'importe' => $validated['importe'],
                'fecha_factura' => $validated['fecha_factura'],
                'periodo' => Carbon::createFromFormat('Y-m', $validated['periodo'])->startOfMonth()->toDateString(),
                'estado' => $validated['estado'],
                'fecha_pago' => $this->fecha_pago,
                'comprobante_pago' => $comprobantePath,
                'factura_archivo' => $facturaPath,
                'factura_nombre_sistema' => $facturaNombreSistema,
                'archivo_disponible_online' => $hasOnlineFiles,
                'fecha_archivado_local' => ! $hasOnlineFiles && $this->gasto?->fecha_archivado_local
                    ? Carbon::parse($this->gasto->fecha_archivado_local)->toDateString()
                    : null,
                'notas' => $validated['notas'] ?? null,
                'ajuste_destino' => $validated['ajuste_destino'] ?? 'siguiente_creacion',
            ], $validated['lineItems'], $this->gasto);
        } catch (ValidationException $exception) {
            $this->setErrorBag($exception->validator->errors());

            return;
        }

        session()->flash('status', 'Gasto guardado correctamente.');
        $this->redirectRoute('gastos.show', ['gasto' => $gasto->id], navigate: true);
    }

    public function getConceptosDisponiblesProperty()
    {
        if (! $this->consorcio_id) {
            return collect();
        }

        return ConceptoPresupuesto::query()
            ->join('presupuestos', 'presupuestos.id', '=', 'concepto_presupuestos.presupuesto_id')
            ->where('presupuestos.consorcio_id', $this->consorcio_id)
            ->when($this->periodo !== '', function ($query): void {
                $periodo = Carbon::createFromFormat('Y-m', $this->periodo)->startOfMonth()->toDateString();
                $query->whereDate('presupuestos.periodo', $periodo);
            })
            ->orderByDesc('presupuestos.periodo')
            ->orderBy('orden')
            ->orderBy('concepto_presupuestos.id')
            ->get([
                'concepto_presupuestos.id',
                'concepto_presupuestos.nombre',
                'concepto_presupuestos.tipo',
                'concepto_presupuestos.rubro',
                'concepto_presupuestos.monto_total',
                'concepto_presupuestos.monto_factura_real',
                'presupuestos.periodo as presupuesto_periodo',
                'presupuestos.estado as presupuesto_estado',
            ]);
    }

    /**
     * @return array<int, array{concepto:string, estimado:float, real:float, diferencia:float}>
     */
    public function getAjustePreviewProperty(): array
    {
        if (! $this->consorcio_id) {
            return [];
        }

        return app(GastoService::class)->previewAjustes($this->lineItems, $this->consorcio_id);
    }

    public function getMontoAsignadoTotalProperty(): float
    {
        return (float) collect($this->sanitizeLineItems($this->lineItems))
            ->sum(fn ($line) => (float) ($line['importe_asignado'] ?? 0));
    }

    public function render()
    {
        return view('livewire.gastos.gasto-editor', [
            'consorcios' => Consorcio::query()->orderBy('nombre')->get(['id', 'nombre']),
            'proveedores' => DB::table('proveedores')
                ->whereNull('deleted_at')
                ->where('activo', true)
                ->orderBy('nombre')
                ->get(['id', 'nombre']),
            'conceptosDisponibles' => $this->conceptosDisponibles,
            'estados' => EstadoGasto::cases(),
            'montoAsignadoTotal' => $this->montoAsignadoTotal,
            'ajustePreview' => $this->ajustePreview,
            'cargaAdjuntosBloqueada' => GastoOnlineRetention::anyPastDeadlineBlocking(),
        ])->layout('layouts.app', ['active' => 'gastos']);
    }

    /**
     * @param  array<int, array{concepto_presupuesto_id:string, importe_asignado:string}>  $items
     * @return array<int, array{concepto_presupuesto_id:string, importe_asignado:string}>
     */
    private function sanitizeLineItems(array $items): array
    {
        $records = $this->extractLineItemRecords($this->extractSynthValue($items));

        return collect($records)
            ->map(function (array $item): array {
                $item = $this->normalizeLineItemPayload($item);
                $concepto = trim((string) ($item['concepto_presupuesto_id'] ?? ''));
                $importeRaw = trim((string) ($item['importe_asignado'] ?? ''));

                return [
                    'concepto_presupuesto_id' => $concepto,
                    'importe_asignado' => str_replace(',', '.', $importeRaw),
                ];
            })
            ->filter(function (array $item): bool {
                return ! ($item['concepto_presupuesto_id'] === '' && ($item['importe_asignado'] === '' || $item['importe_asignado'] === '0' || $item['importe_asignado'] === '0.00'));
            })
            ->values()
            ->all();
    }

    private function normalizeNullableForeignKey(mixed $value): ?int
    {
        $value = $this->extractSynthValue($value);

        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    /**
     * Importe total del comprobante: vacío → null para que "required" falle de forma coherente.
     */
    private function normalizeImporteScalar(mixed $value): ?string
    {
        $value = $this->extractSynthValue($value);

        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        return str_replace(',', '.', trim((string) $value));
    }

    private function normalizeNullableDate(mixed $value): ?string
    {
        $value = $this->extractSynthValue($value);
        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }

    /**
     * Livewire serializa arrays como tuplas [valor, {s: "..."}] en algunos ciclos de hidratación.
     * Esta función devuelve siempre el valor real, sin metadatos de síntesis.
     */
    private function extractSynthValue(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if (array_key_exists(0, $value)
            && array_key_exists(1, $value)
            && is_array($value[1])
            && array_key_exists('s', $value[1])) {
            return $this->extractSynthValue($value[0]);
        }

        return $value;
    }

    /**
     * @param  array<mixed>  $item
     * @return array{concepto_presupuesto_id:mixed, importe_asignado:mixed}
     */
    private function normalizeLineItemPayload(array $item): array
    {
        $normalized = $this->extractSynthValue($item);
        if (! is_array($normalized)) {
            return ['concepto_presupuesto_id' => '', 'importe_asignado' => ''];
        }

        return [
            'concepto_presupuesto_id' => $normalized['concepto_presupuesto_id'] ?? '',
            'importe_asignado' => $normalized['importe_asignado'] ?? '',
        ];
    }

    /**
     * @return array<int, array{concepto_presupuesto_id:mixed, importe_asignado:mixed}>
     */
    private function extractLineItemRecords(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        if (array_key_exists('concepto_presupuesto_id', $value) || array_key_exists('importe_asignado', $value)) {
            return [[
                'concepto_presupuesto_id' => $value['concepto_presupuesto_id'] ?? '',
                'importe_asignado' => $value['importe_asignado'] ?? '',
            ]];
        }

        $records = [];
        foreach ($value as $child) {
            $normalizedChild = $this->extractSynthValue($child);
            foreach ($this->extractLineItemRecords($normalizedChild) as $record) {
                $records[] = $record;
            }
        }

        return $records;
    }
}
