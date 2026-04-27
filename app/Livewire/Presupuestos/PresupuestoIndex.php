<?php

namespace App\Livewire\Presupuestos;

use App\Enums\EstadoPresupuesto;
use App\Models\Consorcio;
use App\Models\Presupuesto;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class PresupuestoIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $consorcioFilter = '';
    public string $estadoFilter = '';
    public string $periodoFilter = '';

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

    public function updatedPeriodoFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $consorcios = Consorcio::query()->orderBy('nombre')->get(['id', 'nombre']);

        $presupuestos = Presupuesto::query()
            ->with(['consorcio:id,nombre'])
            ->withCount('conceptos')
            ->when($this->consorcioFilter !== '', fn (Builder $q) => $q->where('consorcio_id', (int) $this->consorcioFilter))
            ->when($this->estadoFilter !== '', fn (Builder $q) => $q->where('estado', $this->estadoFilter))
            ->when($this->periodoFilter !== '', function (Builder $q): void {
                $periodo = Carbon::createFromFormat('Y-m', $this->periodoFilter)->startOfMonth()->toDateString();
                $q->whereDate('periodo', $periodo);
            })
            ->when($this->search !== '', function (Builder $q): void {
                $term = '%'.$this->search.'%';
                $q->whereHas('consorcio', fn (Builder $c) => $c->where('nombre', 'like', $term));
            })
            ->orderByDesc('periodo')
            ->paginate(10);

        return view('livewire.presupuestos.presupuesto-index', [
            'consorcios' => $consorcios,
            'presupuestos' => $presupuestos,
            'estados' => EstadoPresupuesto::cases(),
        ])->layout('layouts.app', ['active' => 'presupuestos']);
    }
}
