<?php

namespace App\Services;

use App\Enums\EstadoGasto;
use App\Models\Cobranza;
use App\Models\Consorcio;
use App\Models\Gasto;
use App\Models\LiquidacionDetalle;
use App\Models\MovimientoFondo;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class FinanceReportService
{
    /**
     * @return array<string, mixed>
     */
    public function build(int $consorcioId, CarbonImmutable $periodStart): array
    {
        $periodEnd = $periodStart->endOfMonth();
        $consorcio = Consorcio::query()->findOrFail($consorcioId);

        $movements = MovimientoFondo::query()
            ->with('cuenta')
            ->whereHas('cuenta', fn ($q) => $q->where('consorcio_id', $consorcioId))
            ->whereBetween('fecha', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->orderBy('fecha')
            ->get();

        $conciliacion = $this->buildConciliacion($consorcio, $movements, $periodStart, $periodEnd);
        $informe = $this->buildEconomicReport($consorcioId, $periodStart, $periodEnd);
        $deuda = $this->buildDebtReports($consorcioId, $periodStart, $periodEnd);
        $stats = $this->buildStats($consorcioId, $periodStart, $periodEnd, $deuda['capital_liquidado_periodo']);

        return [
            'consorcio' => $consorcio,
            'periodo' => $periodStart,
            'conciliacion' => $conciliacion,
            'informe' => $informe,
            'deuda' => $deuda,
            'stats' => $stats,
        ];
    }

    /**
     * @param  Collection<int, MovimientoFondo>  $movements
     * @return array<string, mixed>
     */
    private function buildConciliacion(Consorcio $consorcio, Collection $movements, CarbonImmutable $periodStart, CarbonImmutable $periodEnd): array
    {
        $ingresos = (float) $movements->where('tipo', 'ingreso')->sum('monto');
        $egresos = (float) $movements->where('tipo', 'egreso')->sum('monto');

        $saldoInicial = 0.0;
        $saldoDisponible = (float) $consorcio->cuentasBancarias()->sum('saldo_actual');
        foreach ($consorcio->cuentasBancarias as $cuenta) {
            $firstMovement = $movements->firstWhere('cuenta_id', $cuenta->id);
            if (! $firstMovement) {
                $saldoInicial += (float) $cuenta->saldo_actual;
                continue;
            }

            $sign = $firstMovement->tipo === 'ingreso' ? 1 : -1;
            $saldoInicial += (float) $firstMovement->saldo_resultante - ($sign * (float) $firstMovement->monto);
        }

        $obligacionesPendientes = (float) Gasto::query()
            ->where('consorcio_id', $consorcio->id)
            ->where('estado', EstadoGasto::Pendiente->value)
            ->sum('importe');

        return [
            'periodo_desde' => $periodStart->toDateString(),
            'periodo_hasta' => $periodEnd->toDateString(),
            'saldo_inicial' => $saldoInicial,
            'ingresos' => $ingresos,
            'egresos' => $egresos,
            'saldo_teorico' => $saldoInicial + $ingresos - $egresos,
            'saldo_disponible' => $saldoDisponible,
            'obligaciones_pendientes' => $obligacionesPendientes,
            'holgura' => $saldoDisponible - $obligacionesPendientes,
            'movimientos' => $movements->take(30),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildEconomicReport(int $consorcioId, CarbonImmutable $periodStart, CarbonImmutable $periodEnd): array
    {
        $gastos = Gasto::query()
            ->with('conceptosPresupuesto:id,nombre,rubro')
            ->where('consorcio_id', $consorcioId)
            ->whereBetween('periodo', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->get();

        $rows = $gastos->flatMap(function (Gasto $gasto) use ($periodStart, $periodEnd): array {
            $fechaPago = $gasto->fecha_pago ? CarbonImmutable::parse($gasto->fecha_pago) : null;

            if ($gasto->conceptosPresupuesto->isEmpty()) {
                return [[
                    'rubro' => 'otros',
                    'concepto' => 'Sin concepto',
                    'devengado' => (float) $gasto->importe,
                    'percibido' => $gasto->estado === EstadoGasto::Pagado
                        && $fechaPago
                        && $fechaPago->betweenIncluded($periodStart, $periodEnd)
                        ? (float) $gasto->importe
                        : 0.0,
                ]];
            }

            return $gasto->conceptosPresupuesto->map(function ($concepto) use ($gasto, $fechaPago, $periodStart, $periodEnd): array {
                $importeAsignado = (float) ($concepto->pivot?->importe_asignado ?? 0);

                return [
                    'rubro' => $concepto->rubro->value,
                    'concepto' => $concepto->nombre,
                    'devengado' => $importeAsignado,
                    'percibido' => $gasto->estado === EstadoGasto::Pagado
                        && $fechaPago
                        && $fechaPago->betweenIncluded($periodStart, $periodEnd)
                        ? $importeAsignado
                        : 0.0,
                ];
            })->all();
        });

        $grouped = collect($rows)
            ->groupBy(fn (array $row) => $row['rubro'].'|'.$row['concepto'])
            ->map(function (Collection $set): array {
                $first = $set->first();

                return [
                    'rubro' => $first['rubro'],
                    'concepto' => $first['concepto'],
                    'devengado' => (float) $set->sum('devengado'),
                    'percibido' => (float) $set->sum('percibido'),
                ];
            })
            ->sortBy('rubro')
            ->values();

        return [
            'rows' => $grouped,
            'totales' => [
                'devengado' => (float) $grouped->sum('devengado'),
                'percibido' => (float) $grouped->sum('percibido'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildDebtReports(int $consorcioId, CarbonImmutable $periodStart, CarbonImmutable $periodEnd): array
    {
        $detallesPeriodo = LiquidacionDetalle::query()
            ->select('liquidacion_detalles.*')
            ->join('liquidacion_conceptos', 'liquidacion_conceptos.id', '=', 'liquidacion_detalles.liquidacion_concepto_id')
            ->join('liquidaciones', 'liquidaciones.id', '=', 'liquidacion_conceptos.liquidacion_id')
            ->where('liquidaciones.consorcio_id', $consorcioId)
            ->whereBetween('liquidaciones.periodo', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->with('unidad:id,numero')
            ->get();

        $capitalLiquidado = (float) $detallesPeriodo->sum('monto_calculado');
        $cobradoPorDetalle = Cobranza::query()
            ->whereIn('liquidacion_detalle_id', $detallesPeriodo->pluck('id')->all())
            ->get()
            ->groupBy('liquidacion_detalle_id')
            ->map(fn (Collection $rows) => (float) $rows->sum('monto_capital'));

        $deudores = $detallesPeriodo
            ->groupBy('unidad_id')
            ->map(function (Collection $rows, int $unidadId) use ($cobradoPorDetalle): ?array {
                $liquidado = (float) $rows->sum('monto_calculado');
                $cobrado = (float) $rows->sum(fn ($row) => $cobradoPorDetalle->get($row->id, 0.0));
                $saldo = $liquidado - $cobrado;
                if ($saldo <= 0.01) {
                    return null;
                }
                $unidad = $rows->first()->unidad;

                return [
                    'unidad_id' => $unidadId,
                    'unidad' => $unidad?->numero ?? 'N/D',
                    'liquidado' => $liquidado,
                    'cobrado' => $cobrado,
                    'saldo' => $saldo,
                ];
            })
            ->filter()
            ->sortByDesc('saldo')
            ->values();

        $proveedoresPendientes = Gasto::query()
            ->with('proveedor:id,nombre')
            ->where('consorcio_id', $consorcioId)
            ->where('estado', EstadoGasto::Pendiente->value)
            ->get()
            ->groupBy('proveedor_id')
            ->map(function (Collection $rows): array {
                $first = $rows->first();

                return [
                    'proveedor' => $first?->proveedor?->nombre ?? 'Sin proveedor',
                    'compromisos' => (int) $rows->count(),
                    'monto_pendiente' => (float) $rows->sum('importe'),
                ];
            })
            ->sortByDesc('monto_pendiente')
            ->values();

        return [
            'deudores' => $deudores,
            'proveedores' => $proveedoresPendientes,
            'capital_liquidado_periodo' => $capitalLiquidado,
        ];
    }

    /**
     * @return array<string, float|int>
     */
    private function buildStats(int $consorcioId, CarbonImmutable $periodStart, CarbonImmutable $periodEnd, float $capitalLiquidado): array
    {
        $cobranzas = Cobranza::query()
            ->where('consorcio_id', $consorcioId)
            ->whereBetween('fecha_pago', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->get();

        $capitalCobrado = (float) $cobranzas->sum('monto_capital');
        $interesCobrado = (float) $cobranzas->sum('monto_interes');

        $cobrabilidad = $capitalLiquidado > 0
            ? round(($capitalCobrado / $capitalLiquidado) * 100, 2)
            : 0.0;

        return [
            'capital_cobrado' => $capitalCobrado,
            'interes_cobrado' => $interesCobrado,
            'total_cobrado' => (float) $cobranzas->sum('total_pagado'),
            'cobrabilidad' => $cobrabilidad,
            'pagos_registrados' => (int) $cobranzas->count(),
        ];
    }
}
