<?php

namespace App\Livewire\Liquidaciones;

use App\Enums\EstadoPresupuesto;
use App\Enums\MetodoDistribucionLiquidacion;
use App\Enums\TipoConceptoPresupuesto;
use App\Models\Liquidacion;
use App\Models\Presupuesto;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class LiquidacionIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $consorcioFilter = '';
    public string $estadoFilter = '';
    public ?int $selectedPresupuestoId = null;
    public bool $showResult = false;
    public bool $readOnly = false;

    /** @var array<int, array{method:string, solo_cocheras:bool, excluded:array<int>, manual:array<int,float>}> */
    public array $conceptConfig = [];

    /** @var array<int, array{ordinario:float, extraordinario:float, total:float, detalles:array<int, array{concepto_id:int,monto:float,coef_aplicado:float,excluido:bool}>}> */
    public array $resultByUnidad = [];
    /** @var array{total_ordinario:float,total_extraordinario:float,total_general:float} */
    public array $totals = ['total_ordinario' => 0.0, 'total_extraordinario' => 0.0, 'total_general' => 0.0];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedConsorcioFilter(): void
    {
        $this->resetPage();
    }

    public function updatedEstadoFilter(): void
    {
        $this->resetPage();
    }

    public function selectPresupuesto(int $presupuestoId): void
    {
        $this->selectedPresupuestoId = $presupuestoId;
        $this->bootstrapConfig();
    }

    public function clearSelection(): void
    {
        $this->selectedPresupuestoId = null;
        $this->conceptConfig = [];
        $this->resultByUnidad = [];
        $this->totals = ['total_ordinario' => 0.0, 'total_extraordinario' => 0.0, 'total_general' => 0.0];
        $this->showResult = false;
        $this->readOnly = false;
    }

    public function setConceptMethod(int $conceptoId, string $method): void
    {
        if ($this->readOnly || ! isset($this->conceptConfig[$conceptoId])) {
            return;
        }

        $allowed = collect(MetodoDistribucionLiquidacion::cases())->map(fn ($case) => $case->value)->all();
        if (! in_array($method, $allowed, true)) {
            return;
        }

        $this->conceptConfig[$conceptoId]['method'] = $method;
        if ($method !== MetodoDistribucionLiquidacion::Manual->value) {
            $this->conceptConfig[$conceptoId]['manual'] = [];
        }
    }

    public function toggleSoloCocheras(int $conceptoId): void
    {
        if ($this->readOnly || ! isset($this->conceptConfig[$conceptoId])) {
            return;
        }

        $nextValue = ! $this->conceptConfig[$conceptoId]['solo_cocheras'];
        $this->conceptConfig[$conceptoId]['solo_cocheras'] = $nextValue;

        $presupuesto = $this->getSelectedPresupuesto();
        if (! $presupuesto) {
            return;
        }

        if ($nextValue) {
            $excluded = $presupuesto->consorcio->unidades
                ->filter(fn ($unidad) => ! $unidad->tiene_cochera)
                ->pluck('id')
                ->values()
                ->all();

            $this->conceptConfig[$conceptoId]['excluded'] = $excluded;
            return;
        }

        $this->conceptConfig[$conceptoId]['excluded'] = [];
    }

    public function toggleExcludeUnit(int $conceptoId, int $unidadId): void
    {
        if ($this->readOnly || ! isset($this->conceptConfig[$conceptoId])) {
            return;
        }

        $current = $this->conceptConfig[$conceptoId]['excluded'];
        if (in_array($unidadId, $current, true)) {
            $this->conceptConfig[$conceptoId]['excluded'] = array_values(array_filter($current, fn ($id) => $id !== $unidadId));
            return;
        }

        $current[] = $unidadId;
        $this->conceptConfig[$conceptoId]['excluded'] = array_values(array_unique($current));
    }

    public function setManual(int $conceptoId, int $unidadId, string $value): void
    {
        if ($this->readOnly || ! isset($this->conceptConfig[$conceptoId])) {
            return;
        }

        $this->conceptConfig[$conceptoId]['manual'][$unidadId] = (float) $value;
    }

    public function calcularLiquidacion(): void
    {
        if ($this->readOnly || ! $this->selectedPresupuestoId) {
            return;
        }

        $presupuesto = $this->getSelectedPresupuesto();
        if (! $presupuesto || $presupuesto->estado !== EstadoPresupuesto::Finalizado) {
            return;
        }

        if (! $this->validateManualDistributions($presupuesto)) {
            return;
        }

        [$resultByUnidad, $totals, $conceptSnapshots] = $this->buildCalculation($presupuesto);
        $this->resultByUnidad = $resultByUnidad;
        $this->totals = $totals;
        $this->showResult = true;

        DB::transaction(function () use ($presupuesto, $totals, $conceptSnapshots): void {
            $periodo = Carbon::parse($presupuesto->periodo);
            $primerVto = $periodo->copy()->day((int) ($presupuesto->dia_primer_vencimiento_real ?? $presupuesto->consorcio->dia_primer_vencimiento ?? 10));
            $segundoVtoDay = (int) ($presupuesto->dia_segundo_vencimiento_real ?? $presupuesto->consorcio->dia_segundo_vencimiento ?? 20);
            $segundoVto = $periodo->copy()->day($segundoVtoDay);
            $recargo = (float) ($presupuesto->recargo_segundo_vto_real ?? $presupuesto->consorcio->recargo_segundo_vto ?? 0);
            $montoSegundoVto = $totals['total_general'] * (1 + ($recargo / 100));

            $liquidacion = Liquidacion::query()->create([
                'presupuesto_id' => $presupuesto->id,
                'consorcio_id' => $presupuesto->consorcio_id,
                'periodo' => Carbon::parse($presupuesto->periodo)->toDateString(),
                'total_ordinario' => $totals['total_ordinario'],
                'total_extraordinario' => $totals['total_extraordinario'],
                'total_general' => $totals['total_general'],
                'fecha_primer_vto' => $primerVto->toDateString(),
                'fecha_segundo_vto' => $segundoVto->toDateString(),
                'monto_segundo_vto' => $montoSegundoVto,
            ]);

            foreach ($conceptSnapshots as $snapshot) {
                $liquidacionConcepto = $liquidacion->conceptos()->create([
                    'concepto_presupuesto_id' => $snapshot['concepto_presupuesto_id'],
                    'nombre' => $snapshot['nombre'],
                    'monto_total' => $snapshot['monto_total'],
                    'tipo' => $snapshot['tipo'],
                    'metodo_distribucion' => $snapshot['metodo_distribucion'],
                    'solo_cocheras' => $snapshot['solo_cocheras'],
                ]);

                foreach ($snapshot['detalles'] as $detalle) {
                    $liquidacionConcepto->detalles()->create($detalle);
                }
            }

            $presupuesto->update(['estado' => EstadoPresupuesto::Liquidado->value]);
        });

        $this->readOnly = true;
        session()->flash('status', 'Liquidación generada correctamente. El presupuesto quedó en estado liquidado.');
    }

    public function render()
    {
        $baseQuery = Presupuesto::query()
            ->with(['consorcio:id,nombre', 'liquidacion:id,presupuesto_id'])
            ->whereIn('estado', [EstadoPresupuesto::Finalizado->value, EstadoPresupuesto::Liquidado->value]);

        $presupuestos = $baseQuery
            ->when($this->consorcioFilter !== '', fn (Builder $q) => $q->where('consorcio_id', (int) $this->consorcioFilter))
            ->when($this->estadoFilter !== '', fn (Builder $q) => $q->where('estado', $this->estadoFilter))
            ->when($this->search !== '', function (Builder $q): void {
                $term = '%'.$this->search.'%';
                $q->whereHas('consorcio', fn (Builder $query) => $query->where('nombre', 'like', $term));
            })
            ->orderByDesc('periodo')
            ->paginate(10);

        $selectedPresupuesto = $this->getSelectedPresupuesto();

        return view('livewire.liquidaciones.liquidacion-index', [
            'presupuestos' => $presupuestos,
            'consorcios' => \App\Models\Consorcio::query()->orderBy('nombre')->get(['id', 'nombre']),
            'selectedPresupuesto' => $selectedPresupuesto,
            'unidades' => $selectedPresupuesto?->consorcio?->unidades ?? collect(),
            'metodos' => MetodoDistribucionLiquidacion::cases(),
        ])->layout('layouts.app', ['active' => 'liquidaciones']);
    }

    private function bootstrapConfig(): void
    {
        $presupuesto = $this->getSelectedPresupuesto();
        if (! $presupuesto) {
            return;
        }

        if ($presupuesto->estado === EstadoPresupuesto::Liquidado && $presupuesto->liquidacion) {
            $this->readOnly = true;
            $this->loadFromLiquidacion($presupuesto->liquidacion);
            return;
        }

        $this->readOnly = false;
        $this->showResult = false;
        $this->resultByUnidad = [];
        $this->totals = ['total_ordinario' => 0.0, 'total_extraordinario' => 0.0, 'total_general' => 0.0];
        $this->conceptConfig = [];

        foreach ($presupuesto->conceptos as $concepto) {
            $this->conceptConfig[$concepto->id] = [
                'method' => MetodoDistribucionLiquidacion::Coeficiente->value,
                'solo_cocheras' => false,
                'excluded' => [],
                'manual' => [],
            ];
        }
    }

    private function loadFromLiquidacion(Liquidacion $liquidacion): void
    {
        $liquidacion->load(['conceptos.detalles']);
        $this->conceptConfig = [];

        foreach ($liquidacion->conceptos as $concepto) {
            $manual = [];
            $excluded = [];
            foreach ($concepto->detalles as $detalle) {
                if ($detalle->excluido) {
                    $excluded[] = $detalle->unidad_id;
                }
                if ($detalle->porcentaje_manual !== null) {
                    $manual[$detalle->unidad_id] = (float) $detalle->porcentaje_manual;
                }
            }

            $this->conceptConfig[$concepto->concepto_presupuesto_id ?? $concepto->id] = [
                'method' => $concepto->metodo_distribucion->value,
                'solo_cocheras' => $concepto->solo_cocheras,
                'excluded' => $excluded,
                'manual' => $manual,
            ];
        }

        $this->totals = [
            'total_ordinario' => (float) $liquidacion->total_ordinario,
            'total_extraordinario' => (float) $liquidacion->total_extraordinario,
            'total_general' => (float) $liquidacion->total_general,
        ];
        $this->showResult = true;

        [$resultByUnidad] = $this->buildCalculation($this->getSelectedPresupuesto());
        $this->resultByUnidad = $resultByUnidad;
    }

    private function getSelectedPresupuesto(): ?Presupuesto
    {
        if (! $this->selectedPresupuestoId) {
            return null;
        }

        return Presupuesto::query()
            ->with([
                'consorcio:id,nombre,dia_primer_vencimiento,dia_segundo_vencimiento,recargo_segundo_vto',
                'consorcio.unidades:id,consorcio_id,numero,coeficiente,tiene_cochera',
                'consorcio.unidades.propietario:id,unidad_id,nombre',
                'consorcio.unidades.inquilino:id,unidad_id,nombre,apellido',
                'conceptos' => fn ($q) => $q->orderBy('orden')->orderBy('id'),
                'liquidacion',
            ])
            ->find($this->selectedPresupuestoId);
    }

    private function validateManualDistributions(Presupuesto $presupuesto): bool
    {
        foreach ($presupuesto->conceptos as $concepto) {
            $config = $this->conceptConfig[$concepto->id] ?? null;
            if (! $config || $config['method'] !== MetodoDistribucionLiquidacion::Manual->value) {
                continue;
            }

            $excluded = collect($config['excluded']);
            if ($config['solo_cocheras']) {
                $excluded = $excluded->merge(
                    $presupuesto->consorcio->unidades
                        ->filter(fn ($unidad) => ! $unidad->tiene_cochera)
                        ->pluck('id')
                );
            }
            $excludedIds = $excluded->unique()->values()->all();
            $activos = $presupuesto->consorcio->unidades->reject(fn ($unidad) => in_array($unidad->id, $excludedIds, true));

            $sum = 0.0;
            foreach ($activos as $unidad) {
                $sum += (float) ($config['manual'][$unidad->id] ?? 0);
            }

            if (abs($sum - 100) > 0.1) {
                $this->addError(
                    'manual_distribution',
                    'El concepto "'.$concepto->nombre.'" tiene distribución manual inválida (suma '.number_format($sum, 2, ',', '.').'%, debe ser 100%).'
                );

                return false;
            }
        }

        $this->resetErrorBag('manual_distribution');

        return true;
    }

    /**
     * @return array{0:array<int,array{ordinario:float,extraordinario:float,total:float,detalles:array<int,array{concepto_id:int,monto:float,coef_aplicado:float,excluido:bool}>>>,1:array{total_ordinario:float,total_extraordinario:float,total_general:float},2:array<int,array<string,mixed>>}
     */
    private function buildCalculation(?Presupuesto $presupuesto): array
    {
        if (! $presupuesto) {
            return [[], ['total_ordinario' => 0.0, 'total_extraordinario' => 0.0, 'total_general' => 0.0], []];
        }

        $unidades = $presupuesto->consorcio->unidades->values();
        $resultByUnidad = [];
        foreach ($unidades as $unidad) {
            $resultByUnidad[$unidad->id] = [
                'ordinario' => 0.0,
                'extraordinario' => 0.0,
                'total' => 0.0,
                'detalles' => [],
            ];
        }

        $snapshots = [];
        foreach ($presupuesto->conceptos as $concepto) {
            $config = $this->conceptConfig[$concepto->id] ?? [
                'method' => MetodoDistribucionLiquidacion::Coeficiente->value,
                'solo_cocheras' => false,
                'excluded' => [],
                'manual' => [],
            ];

            $excluded = collect($config['excluded']);
            if ($config['solo_cocheras']) {
                $excluded = $excluded->merge(
                    $unidades->filter(fn ($unidad) => ! $unidad->tiene_cochera)->pluck('id')
                );
            }
            $excludedIds = $excluded->unique()->values()->all();

            $activos = $unidades->reject(fn ($unidad) => in_array($unidad->id, $excludedIds, true));
            if ($activos->isEmpty()) {
                continue;
            }

            $sumCoef = max(0.000001, (float) $activos->sum('coeficiente'));
            $sumManual = max(0.000001, (float) collect($config['manual'])->sum());
            $detalles = [];

            foreach ($unidades as $unidad) {
                $isExcluded = in_array($unidad->id, $excludedIds, true);
                $coefAplicado = 0.0;
                $porcentajeManual = null;
                $monto = 0.0;

                if (! $isExcluded) {
                    if ($config['method'] === MetodoDistribucionLiquidacion::Coeficiente->value) {
                        $coefAplicado = ((float) $unidad->coeficiente / $sumCoef) * 100;
                        $monto = (float) $concepto->monto_total * ($coefAplicado / 100);
                    } elseif ($config['method'] === MetodoDistribucionLiquidacion::PartesIguales->value) {
                        $coefAplicado = 100 / max(1, $activos->count());
                        $monto = (float) $concepto->monto_total / max(1, $activos->count());
                    } else {
                        $manual = (float) ($config['manual'][$unidad->id] ?? 0);
                        $porcentajeManual = $manual;
                        $coefAplicado = $manual;
                        $monto = (float) $concepto->monto_total * ($manual / $sumManual);
                    }
                }

                if ($concepto->tipo === TipoConceptoPresupuesto::Ordinario) {
                    $resultByUnidad[$unidad->id]['ordinario'] += $monto;
                } else {
                    $resultByUnidad[$unidad->id]['extraordinario'] += $monto;
                }
                $resultByUnidad[$unidad->id]['total'] += $monto;
                $resultByUnidad[$unidad->id]['detalles'][] = [
                    'concepto_id' => $concepto->id,
                    'monto' => $monto,
                    'coef_aplicado' => $coefAplicado,
                    'excluido' => $isExcluded,
                ];

                $detalles[] = [
                    'unidad_id' => $unidad->id,
                    'coeficiente_aplicado' => $coefAplicado,
                    'monto_calculado' => $monto,
                    'excluido' => $isExcluded,
                    'porcentaje_manual' => $porcentajeManual,
                ];
            }

            $snapshots[] = [
                'concepto_presupuesto_id' => $concepto->id,
                'nombre' => $concepto->nombre,
                'monto_total' => (float) $concepto->monto_total,
                'tipo' => $concepto->tipo->value,
                'metodo_distribucion' => $config['method'],
                'solo_cocheras' => (bool) $config['solo_cocheras'],
                'detalles' => $detalles,
            ];
        }

        $totalOrdinario = (float) collect($resultByUnidad)->sum('ordinario');
        $totalExtraordinario = (float) collect($resultByUnidad)->sum('extraordinario');

        return [
            $resultByUnidad,
            [
                'total_ordinario' => $totalOrdinario,
                'total_extraordinario' => $totalExtraordinario,
                'total_general' => $totalOrdinario + $totalExtraordinario,
            ],
            $snapshots,
        ];
    }
}
