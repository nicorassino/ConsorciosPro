<?php

namespace App\Services;

use App\Enums\EstadoGasto;
use App\Enums\EstadoPresupuesto;
use App\Models\ConceptoPresupuesto;
use App\Models\Consorcio;
use App\Models\Gasto;
use App\Models\Presupuesto;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GastoService
{
    /** Texto fijo de conceptos "Ajuste …" creados por el sistema (listados, presupuesto, tests). */
    public const AJUSTE_DESCRIPCION_AUTO = 'Generado automáticamente por diferencia con factura real del período anterior.';

    /**
     * @param  array<int, array{concepto_presupuesto_id:int, importe_asignado:numeric-string|float|int}>  $lineItems
     */
    public function save(array $attributes, array $lineItems, ?Gasto $gasto = null): Gasto
    {
        return DB::transaction(function () use ($attributes, $lineItems, $gasto): Gasto {
            $normalizedPeriodo = Carbon::parse($attributes['periodo'])->startOfMonth()->toDateString();
            $normalizedImporte = round((float) $attributes['importe'], 2);
            $normalizedItems = $this->normalizeItems($lineItems);

            $this->assertLinesMatchTotal($normalizedImporte, $normalizedItems);
            $this->assertConceptosBelongToConsorcio((int) $attributes['consorcio_id'], $normalizedItems);

            $gastoModel = $gasto ?? new Gasto;
            $previousConceptIds = $gastoModel->exists
                ? $gastoModel->conceptosPresupuesto()->pluck('concepto_presupuesto_id')->all()
                : [];

            $payload = [
                'consorcio_id' => (int) $attributes['consorcio_id'],
                'proveedor_id' => $attributes['proveedor_id'] !== null ? (int) $attributes['proveedor_id'] : null,
                'nro_orden' => (string) $attributes['nro_orden'],
                'descripcion' => (string) $attributes['descripcion'],
                'importe' => $normalizedImporte,
                'fecha_factura' => Carbon::parse($attributes['fecha_factura'])->toDateString(),
                'periodo' => $normalizedPeriodo,
                'estado' => $attributes['estado'] ?? EstadoGasto::Pendiente->value,
                'fecha_pago' => ! empty($attributes['fecha_pago']) ? Carbon::parse($attributes['fecha_pago'])->toDateString() : null,
                'comprobante_pago' => $attributes['comprobante_pago'] ?? null,
                'factura_archivo' => $attributes['factura_archivo'] ?? null,
                'factura_nombre_sistema' => $attributes['factura_nombre_sistema'] ?? null,
                'archivo_disponible_online' => (bool) ($attributes['archivo_disponible_online'] ?? true),
                'fecha_archivado_local' => $attributes['fecha_archivado_local'] ?? null,
                'notas' => $attributes['notas'] ?? null,
            ];

            $gastoModel->fill($payload)->save();

            $syncPayload = [];
            foreach ($normalizedItems as $item) {
                $syncPayload[$item['concepto_presupuesto_id']] = [
                    'importe_asignado' => $item['importe_asignado'],
                ];
            }
            $gastoModel->conceptosPresupuesto()->sync($syncPayload);

            $affectedConceptIds = array_values(array_unique(array_merge(
                $previousConceptIds,
                array_column($normalizedItems, 'concepto_presupuesto_id')
            )));
            $this->syncMontoFacturaReal($affectedConceptIds);
            $this->syncAjustesDesdeFacturas(
                $affectedConceptIds,
                (int) $attributes['consorcio_id'],
                (string) ($attributes['ajuste_destino'] ?? 'siguiente_creacion')
            );

            return $gastoModel->fresh(['consorcio', 'proveedor', 'conceptosPresupuesto']);
        });
    }

    public function delete(Gasto $gasto): void
    {
        DB::transaction(function () use ($gasto): void {
            $conceptIds = $gasto->conceptosPresupuesto()->pluck('concepto_presupuesto_id')->all();
            $gasto->delete();
            $this->syncMontoFacturaReal($conceptIds);
        });
    }

    /**
     * @param  array<int, array{concepto_presupuesto_id:int, importe_asignado:numeric-string|float|int}>  $lineItems
     */
    public function buildSystemFilename(int $consorcioId, string $periodo, array $lineItems, ?string $extension = null): string
    {
        $consorcio = Consorcio::query()->find($consorcioId);
        $consorcioSlug = Str::slug($consorcio?->nombre ?? 'consorcio');
        $periodoYm = Carbon::parse($periodo)->format('Y-m');

        $conceptIds = collect($this->normalizeItems($lineItems))
            ->pluck('concepto_presupuesto_id')
            ->unique()
            ->values();

        $conceptSlug = 'varios';
        if ($conceptIds->count() === 1) {
            $conceptName = ConceptoPresupuesto::query()->whereKey((int) $conceptIds->first())->value('nombre');
            $conceptSlug = Str::slug((string) ($conceptName ?: 'concepto'));
        }

        $ext = strtolower((string) ($extension ?: 'pdf'));

        return sprintf('%s_%s_%s.%s', $conceptSlug, $periodoYm, $consorcioSlug, $ext);
    }

    /**
     * @param  array<int, array{concepto_presupuesto_id:int|string|null, importe_asignado:numeric-string|float|int|null}>  $lineItems
     * @return array<int, array{concepto:string, estimado:float, real:float, diferencia:float}>
     */
    public function previewAjustes(array $lineItems, int $consorcioId): array
    {
        $normalizedItems = $this->normalizeItems($lineItems);
        if ($normalizedItems === []) {
            return [];
        }

        $ids = array_column($normalizedItems, 'concepto_presupuesto_id');
        $conceptos = ConceptoPresupuesto::query()
            ->join('presupuestos', 'presupuestos.id', '=', 'concepto_presupuestos.presupuesto_id')
            ->whereIn('concepto_presupuestos.id', $ids)
            ->where('presupuestos.consorcio_id', $consorcioId)
            ->get([
                'concepto_presupuestos.id',
                'concepto_presupuestos.nombre',
                'concepto_presupuestos.monto_total',
            ])
            ->keyBy('id');

        $preview = [];
        foreach ($normalizedItems as $item) {
            $concepto = $conceptos->get($item['concepto_presupuesto_id']);
            if (! $concepto) {
                continue;
            }

            $estimado = round((float) $concepto->monto_total, 2);
            $real = round((float) $item['importe_asignado'], 2);
            $diferencia = round($real - $estimado, 2);

            if ($diferencia === 0.0) {
                continue;
            }

            $preview[] = [
                'concepto' => (string) $concepto->nombre,
                'estimado' => $estimado,
                'real' => $real,
                'diferencia' => $diferencia,
            ];
        }

        return $preview;
    }

    public function marcarArchivoLocal(Gasto $gasto): void
    {
        if ($gasto->factura_archivo && Storage::disk('local')->exists($gasto->factura_archivo)) {
            Storage::disk('local')->delete($gasto->factura_archivo);
        }
        if ($gasto->comprobante_pago && Storage::disk('local')->exists($gasto->comprobante_pago)) {
            Storage::disk('local')->delete($gasto->comprobante_pago);
        }

        $gasto->update([
            'archivo_disponible_online' => false,
            'fecha_archivado_local' => now()->toDateString(),
            'factura_archivo' => null,
            'comprobante_pago' => null,
        ]);
    }

    /**
     * @param  array<int>  $conceptoIds
     */
    public function syncMontoFacturaReal(array $conceptoIds): void
    {
        $uniqueIds = array_values(array_unique(array_map('intval', $conceptoIds)));
        if ($uniqueIds === []) {
            return;
        }

        $aggregates = DB::table('gasto_concepto_presupuesto as gcp')
            ->join('gastos as g', 'g.id', '=', 'gcp.gasto_id')
            ->whereNull('g.deleted_at')
            ->whereIn('gcp.concepto_presupuesto_id', $uniqueIds)
            ->groupBy('gcp.concepto_presupuesto_id')
            ->selectRaw('gcp.concepto_presupuesto_id, ROUND(SUM(gcp.importe_asignado), 2) as total')
            ->pluck('total', 'gcp.concepto_presupuesto_id');

        foreach ($uniqueIds as $conceptoId) {
            $total = $aggregates[$conceptoId] ?? null;
            ConceptoPresupuesto::query()
                ->whereKey($conceptoId)
                ->update(['monto_factura_real' => $total !== null ? (float) $total : null]);
        }
    }

    /**
     * @param  array<int>  $conceptoIds
     */
    public function syncAjustesDesdeFacturas(array $conceptoIds, int $consorcioId, string $ajusteDestino): void
    {
        $uniqueIds = array_values(array_unique(array_map('intval', $conceptoIds)));
        if ($uniqueIds === []) {
            return;
        }

        if ($ajusteDestino !== 'ultimo_pendiente') {
            return;
        }

        $presupuestoPendiente = Presupuesto::query()
            ->where('consorcio_id', $consorcioId)
            ->where('estado', EstadoPresupuesto::Borrador->value)
            ->orderByDesc('periodo')
            ->first();

        if (! $presupuestoPendiente) {
            return;
        }

        $conceptosOrigen = ConceptoPresupuesto::query()
            ->with('presupuesto:id,consorcio_id,periodo')
            ->whereIn('id', $uniqueIds)
            ->get();

        foreach ($conceptosOrigen as $conceptoOrigen) {
            $diferencia = round((float) ($conceptoOrigen->monto_factura_real ?? 0) - (float) $conceptoOrigen->monto_total, 2);

            $ajusteExistente = ConceptoPresupuesto::query()
                ->where('presupuesto_id', $presupuestoPendiente->id)
                ->where('nombre', 'Ajuste '.$conceptoOrigen->nombre)
                ->where('descripcion', self::AJUSTE_DESCRIPCION_AUTO)
                ->first();

            if ($diferencia === 0.0) {
                if ($ajusteExistente) {
                    $ajusteExistente->delete();
                }
                continue;
            }

            if ($ajusteExistente) {
                $ajusteExistente->update([
                    'monto_total' => $diferencia,
                    'tipo' => $conceptoOrigen->tipo->value,
                    'rubro' => $conceptoOrigen->rubro->value,
                ]);
                continue;
            }

            $nextOrder = (int) ConceptoPresupuesto::query()
                ->where('presupuesto_id', $presupuestoPendiente->id)
                ->max('orden');

            ConceptoPresupuesto::query()->create([
                'presupuesto_id' => $presupuestoPendiente->id,
                'nombre' => 'Ajuste '.$conceptoOrigen->nombre,
                'rubro' => $conceptoOrigen->rubro->value,
                'descripcion' => self::AJUSTE_DESCRIPCION_AUTO,
                'monto_total' => $diferencia,
                'cuotas_total' => 1,
                'cuota_actual' => 1,
                'tipo' => $conceptoOrigen->tipo->value,
                'aplica_cocheras' => false,
                'orden' => $nextOrder + 1,
            ]);
        }
    }

    /**
     * @param  array<int, array{concepto_presupuesto_id:int, importe_asignado:float}>  $lineItems
     */
    private function assertLinesMatchTotal(float $importe, array $lineItems): void
    {
        if ($lineItems === []) {
            throw ValidationException::withMessages([
                'lineItems' => 'Debe asignar al menos un concepto del presupuesto.',
            ]);
        }

        $sum = round((float) collect($lineItems)->sum('importe_asignado'), 2);
        if (abs($sum - $importe) > 0.01) {
            throw ValidationException::withMessages([
                'lineItems' => 'La suma imputada a conceptos debe coincidir con el importe total del gasto.',
            ]);
        }
    }

    /**
     * @param  array<int, array{concepto_presupuesto_id:int, importe_asignado:float}>  $lineItems
     */
    private function assertConceptosBelongToConsorcio(int $consorcioId, array $lineItems): void
    {
        $ids = array_column($lineItems, 'concepto_presupuesto_id');

        $matched = ConceptoPresupuesto::query()
            ->join('presupuestos', 'presupuestos.id', '=', 'concepto_presupuestos.presupuesto_id')
            ->whereIn('concepto_presupuestos.id', $ids)
            ->where('presupuestos.consorcio_id', $consorcioId)
            ->count();

        if ($matched !== count($ids)) {
            throw ValidationException::withMessages([
                'lineItems' => 'Uno o más conceptos seleccionados no pertenecen al consorcio del gasto.',
            ]);
        }
    }

    /**
     * @param  array<int, array{concepto_presupuesto_id:int|string|null, importe_asignado:numeric-string|float|int|null}>  $lineItems
     * @return array<int, array{concepto_presupuesto_id:int, importe_asignado:float}>
     */
    private function normalizeItems(array $lineItems): array
    {
        $normalized = [];

        foreach ($lineItems as $item) {
            $conceptoId = (int) ($item['concepto_presupuesto_id'] ?? 0);
            $importeAsignado = round((float) ($item['importe_asignado'] ?? 0), 2);

            if ($conceptoId <= 0) {
                continue;
            }

            if (! isset($normalized[$conceptoId])) {
                $normalized[$conceptoId] = 0.0;
            }

            $normalized[$conceptoId] = round($normalized[$conceptoId] + $importeAsignado, 2);
        }

        return collect($normalized)
            ->map(fn (float $importe, int $conceptoId): array => [
                'concepto_presupuesto_id' => $conceptoId,
                'importe_asignado' => $importe,
            ])
            ->values()
            ->all();
    }
}
