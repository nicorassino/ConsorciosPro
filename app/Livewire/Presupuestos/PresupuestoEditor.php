<?php

namespace App\Livewire\Presupuestos;

use App\Enums\EstadoPresupuesto;
use App\Enums\RubroConceptoPresupuesto;
use App\Enums\TipoConceptoPresupuesto;
use App\Models\ConceptoPresupuesto;
use App\Models\Consorcio;
use App\Models\Presupuesto;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

class PresupuestoEditor extends Component
{
    private const AJUSTE_DESCRIPCION_AUTO = 'Generado automáticamente por diferencia con factura real del período anterior.';

    public ?Presupuesto $presupuesto = null;
    public bool $isCreateMode = false;
    public bool $showConceptModal = false;
    public ?int $editingConceptId = null;

    public ?int $consorcio_id = null;
    public string $periodo = '';
    public string $notas = '';

    public string $concepto_nombre = '';
    public string $concepto_tipo = '';
    public string $concepto_rubro = '';
    public ?string $concepto_monto_total = null;
    public int $concepto_cuotas_total = 1;
    public int $concepto_cuota_actual = 1;
    public string $concepto_descripcion = '';
    public bool $concepto_aplica_cocheras = false;

    public function mount(?Presupuesto $presupuesto = null): void
    {
        $this->concepto_tipo = TipoConceptoPresupuesto::Ordinario->value;
        $this->concepto_rubro = RubroConceptoPresupuesto::Servicios->value;

        if ($presupuesto) {
            $this->isCreateMode = false;
            $this->presupuesto = $this->loadPresupuesto($presupuesto->id);
            return;
        }

        $this->isCreateMode = true;
        $this->periodo = now()->format('Y-m');
    }

    public function createPresupuesto(): void
    {
        $this->validate([
            'consorcio_id' => ['required', 'exists:consorcios,id'],
            'periodo' => [
                'required',
                'date_format:Y-m',
                Rule::unique('presupuestos', 'periodo')
                    ->where(fn (QueryBuilder $query) => $query->where('consorcio_id', $this->consorcio_id)),
            ],
            'notas' => ['nullable', 'string', 'max:65535'],
        ], [
            'periodo.unique' => 'Ya existe un presupuesto para este consorcio en el período seleccionado.',
        ]);

        $periodoDate = Carbon::createFromFormat('Y-m', $this->periodo)->startOfMonth();
        $consorcio = Consorcio::query()->findOrFail($this->consorcio_id);

        $presupuesto = DB::transaction(function () use ($periodoDate, $consorcio): Presupuesto {
            $newPresupuesto = Presupuesto::query()->create([
                'consorcio_id' => $consorcio->id,
                'periodo' => $periodoDate->toDateString(),
                'estado' => EstadoPresupuesto::Borrador->value,
                'dia_primer_vencimiento_real' => $consorcio->dia_primer_vencimiento,
                'dia_segundo_vencimiento_real' => $consorcio->dia_segundo_vencimiento,
                'recargo_segundo_vto_real' => $consorcio->recargo_segundo_vto,
                'notas' => $this->notas !== '' ? $this->notas : null,
            ]);

            $this->cloneFromPreviousMonth($newPresupuesto);

            return $newPresupuesto;
        });

        session()->flash('status', 'Presupuesto creado correctamente.');
        $this->redirectRoute('presupuestos.show', ['presupuesto' => $presupuesto->id], navigate: true);
    }

    public function finalize(): void
    {
        if (! $this->presupuesto || ! $this->canEdit()) {
            return;
        }

        $this->presupuesto->update(['estado' => EstadoPresupuesto::Finalizado->value]);
        $this->presupuesto = $this->loadPresupuesto($this->presupuesto->id);
        session()->flash('status', 'Presupuesto finalizado. Queda en modo solo lectura.');
    }

    public function openCreateConceptModal(): void
    {
        if (! $this->canEdit()) {
            return;
        }

        $this->resetConceptForm();
        $this->showConceptModal = true;
    }

    public function openEditConceptModal(int $conceptoId): void
    {
        if (! $this->canEdit() || ! $this->presupuesto) {
            return;
        }

        $concepto = $this->presupuesto->conceptos()->findOrFail($conceptoId);
        $this->editingConceptId = $concepto->id;
        $this->concepto_nombre = $concepto->nombre;
        $this->concepto_tipo = $concepto->tipo->value;
        $this->concepto_rubro = $concepto->rubro->value;
        $this->concepto_monto_total = (string) $concepto->monto_total;
        $this->concepto_cuotas_total = $concepto->cuotas_total;
        $this->concepto_cuota_actual = $concepto->cuota_actual;
        $this->concepto_descripcion = $concepto->descripcion ?? '';
        $this->concepto_aplica_cocheras = $concepto->aplica_cocheras;
        $this->showConceptModal = true;
    }

    public function closeConceptModal(): void
    {
        $this->showConceptModal = false;
        $this->resetConceptForm();
    }

    public function saveConcept(): void
    {
        if (! $this->canEdit() || ! $this->presupuesto) {
            return;
        }

        $this->validate([
            'concepto_nombre' => ['required', 'string', 'max:191'],
            'concepto_tipo' => ['required', Rule::enum(TipoConceptoPresupuesto::class)],
            'concepto_rubro' => ['required', Rule::enum(RubroConceptoPresupuesto::class)],
            'concepto_monto_total' => ['required', 'numeric', 'min:0'],
            'concepto_cuotas_total' => ['required', 'integer', 'min:1', 'max:60'],
            'concepto_cuota_actual' => ['required', 'integer', 'min:1', 'max:60', 'lte:concepto_cuotas_total'],
            'concepto_descripcion' => ['nullable', 'string', 'max:65535'],
        ]);

        $data = [
            'nombre' => $this->concepto_nombre,
            'tipo' => $this->concepto_tipo,
            'rubro' => $this->concepto_rubro,
            'monto_total' => $this->concepto_monto_total,
            'cuotas_total' => $this->concepto_cuotas_total,
            'cuota_actual' => $this->concepto_cuota_actual,
            'descripcion' => $this->concepto_descripcion !== '' ? $this->concepto_descripcion : null,
            'aplica_cocheras' => $this->concepto_aplica_cocheras,
        ];

        if ($this->editingConceptId) {
            $this->presupuesto->conceptos()->findOrFail($this->editingConceptId)->update($data);
            session()->flash('status', 'Concepto actualizado.');
        } else {
            $data['orden'] = ((int) $this->presupuesto->conceptos()->max('orden')) + 1;
            $this->presupuesto->conceptos()->create($data);
            session()->flash('status', 'Concepto agregado.');
        }

        $this->presupuesto = $this->loadPresupuesto($this->presupuesto->id);
        $this->closeConceptModal();
    }

    public function deleteConcept(int $conceptoId): void
    {
        if (! $this->canEdit() || ! $this->presupuesto) {
            return;
        }

        $this->presupuesto->conceptos()->findOrFail($conceptoId)->delete();
        $this->presupuesto = $this->loadPresupuesto($this->presupuesto->id);
        session()->flash('status', 'Concepto eliminado.');
    }

    public function render()
    {
        $consorcios = Consorcio::query()->orderBy('nombre')->get(['id', 'nombre']);

        $totalOrdinario = 0.0;
        $totalExtraordinario = 0.0;
        if ($this->presupuesto) {
            foreach ($this->presupuesto->conceptos as $concepto) {
                $monto = $concepto->monto_factura_real ?? $concepto->monto_total;
                if ($concepto->tipo === TipoConceptoPresupuesto::Ordinario) {
                    $totalOrdinario += (float) $monto;
                } else {
                    $totalExtraordinario += (float) $monto;
                }
            }
        }

        return view('livewire.presupuestos.presupuesto-editor', [
            'consorcios' => $consorcios,
            'canEdit' => $this->canEdit(),
            'tiposConcepto' => TipoConceptoPresupuesto::cases(),
            'rubrosConcepto' => RubroConceptoPresupuesto::cases(),
            'totalOrdinario' => $totalOrdinario,
            'totalExtraordinario' => $totalExtraordinario,
            'totalGeneral' => $totalOrdinario + $totalExtraordinario,
        ])->layout('layouts.app', ['active' => 'presupuestos']);
    }

    private function canEdit(): bool
    {
        return $this->presupuesto && $this->presupuesto->estado === EstadoPresupuesto::Borrador;
    }

    private function loadPresupuesto(int $id): Presupuesto
    {
        return Presupuesto::query()
            ->with(['consorcio:id,nombre', 'conceptos' => fn ($q) => $q->orderBy('orden')->orderBy('id')])
            ->findOrFail($id);
    }

    private function cloneFromPreviousMonth(Presupuesto $newPresupuesto): void
    {
        $orden = 1;
        $previous = Presupuesto::query()
            ->where('consorcio_id', $newPresupuesto->consorcio_id)
            ->whereDate('periodo', Carbon::parse($newPresupuesto->periodo)->subMonth()->startOfMonth()->toDateString())
            ->with('conceptos')
            ->first();

        if ($previous) {
            $newPresupuesto->update(['presupuesto_anterior_id' => $previous->id]);

            foreach ($previous->conceptos as $conceptoAnterior) {
                $siguienteCuota = $conceptoAnterior->cuotas_total > 1
                    ? $conceptoAnterior->cuota_actual + 1
                    : $conceptoAnterior->cuota_actual;

                if ($conceptoAnterior->cuotas_total > 1 && $siguienteCuota > $conceptoAnterior->cuotas_total) {
                    continue;
                }

                $newPresupuesto->conceptos()->create([
                    'nombre' => $conceptoAnterior->nombre,
                    'rubro' => $conceptoAnterior->rubro->value,
                    'descripcion' => $conceptoAnterior->descripcion,
                    'monto_total' => $conceptoAnterior->monto_total,
                    'cuotas_total' => $conceptoAnterior->cuotas_total,
                    'cuota_actual' => $siguienteCuota,
                    'tipo' => $conceptoAnterior->tipo->value,
                    'aplica_cocheras' => $conceptoAnterior->aplica_cocheras,
                    'orden' => $orden++,
                ]);
            }
        }

        $this->appendPendingAutoAjustes($newPresupuesto, $orden);
    }

    private function appendPendingAutoAjustes(Presupuesto $newPresupuesto, int $startOrder): void
    {
        $targetPeriodo = Carbon::parse($newPresupuesto->periodo)->startOfMonth()->toDateString();
        $orden = $startOrder;

        $origenes = ConceptoPresupuesto::query()
            ->join('presupuestos', 'presupuestos.id', '=', 'concepto_presupuestos.presupuesto_id')
            ->where('presupuestos.consorcio_id', $newPresupuesto->consorcio_id)
            ->whereDate('presupuestos.periodo', '<', $targetPeriodo)
            ->where(function ($query): void {
                $query->whereNull('concepto_presupuestos.descripcion')
                    ->orWhere('concepto_presupuestos.descripcion', '!=', self::AJUSTE_DESCRIPCION_AUTO);
            })
            ->get(['concepto_presupuestos.*', 'presupuestos.periodo as presupuesto_periodo']);

        foreach ($origenes as $conceptoOrigen) {
            $diferencia = round((float) ($conceptoOrigen->monto_factura_real ?? 0) - (float) $conceptoOrigen->monto_total, 2);
            if ($diferencia === 0.0) {
                continue;
            }

            $ajusteNombre = 'Ajuste '.$conceptoOrigen->nombre;
            $yaExisteEnActual = ConceptoPresupuesto::query()
                ->where('presupuesto_id', $newPresupuesto->id)
                ->where('nombre', $ajusteNombre)
                ->where('descripcion', self::AJUSTE_DESCRIPCION_AUTO)
                ->exists();
            if ($yaExisteEnActual) {
                continue;
            }

            $yaFueMaterializado = ConceptoPresupuesto::query()
                ->join('presupuestos', 'presupuestos.id', '=', 'concepto_presupuestos.presupuesto_id')
                ->where('presupuestos.consorcio_id', $newPresupuesto->consorcio_id)
                ->whereDate('presupuestos.periodo', '>', Carbon::parse($conceptoOrigen->presupuesto_periodo)->startOfMonth()->toDateString())
                ->whereDate('presupuestos.periodo', '<', $targetPeriodo)
                ->where('concepto_presupuestos.nombre', $ajusteNombre)
                ->where('concepto_presupuestos.descripcion', self::AJUSTE_DESCRIPCION_AUTO)
                ->exists();
            if ($yaFueMaterializado) {
                continue;
            }

            $newPresupuesto->conceptos()->create([
                'nombre' => $ajusteNombre,
                'rubro' => $conceptoOrigen->rubro->value,
                'descripcion' => self::AJUSTE_DESCRIPCION_AUTO,
                'monto_total' => $diferencia,
                'cuotas_total' => 1,
                'cuota_actual' => 1,
                'tipo' => $conceptoOrigen->tipo->value,
                'aplica_cocheras' => false,
                'orden' => $orden++,
            ]);
        }
    }

    private function resetConceptForm(): void
    {
        $this->editingConceptId = null;
        $this->concepto_nombre = '';
        $this->concepto_tipo = TipoConceptoPresupuesto::Ordinario->value;
        $this->concepto_rubro = RubroConceptoPresupuesto::Servicios->value;
        $this->concepto_monto_total = null;
        $this->concepto_cuotas_total = 1;
        $this->concepto_cuota_actual = 1;
        $this->concepto_descripcion = '';
        $this->concepto_aplica_cocheras = false;
        $this->resetValidation();
    }
}
