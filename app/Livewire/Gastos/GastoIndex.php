<?php

namespace App\Livewire\Gastos;

use App\Enums\EstadoPresupuesto;
use App\Models\ConceptoPresupuesto;
use App\Models\Consorcio;
use App\Models\Gasto;
use App\Models\Presupuesto;
use App\Services\GastoOnlineRetention;
use App\Services\GastoService;
use Carbon\Carbon;
use ZipArchive;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Livewire\Component;
use Livewire\WithPagination;

class GastoIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $consorcioFilter = '';

    public string $estadoFilter = '';

    public string $periodoFilter = '';

    public string $archivoFilter = '';

    /**
     * Mes/año de corte (input type="month"): se archiva todo con fecha_factura estrictamente anterior a este mes.
     */
    public string $archivoCorte = '';

    /** @var array<int, array{has: bool, amount: float}> */
    public array $ajustesMesSiguiente = [];

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

    public function updatedArchivoFilter(): void
    {
        $this->resetPage();
    }

    public function deleteGasto(int $gastoId): void
    {
        $gasto = Gasto::query()->findOrFail($gastoId);
        app(GastoService::class)->delete($gasto);

        session()->flash('status', 'Gasto eliminado correctamente.');
    }

    public function downloadArchivo(int $gastoId, string $tipo): Response
    {
        $gasto = Gasto::query()->findOrFail($gastoId);
        $path = match ($tipo) {
            'factura' => $gasto->factura_archivo,
            'comprobante' => $gasto->comprobante_pago,
            default => null,
        };

        abort_if($path === null || $path === '', 404);
        abort_unless(Storage::disk('local')->exists($path), 404);

        return response()->download(Storage::disk('local')->path($path), basename($path));
    }

    public function ackAvisoAnualArchivo(): void
    {
        session()->put('gastos_archive_notice_ack_'.now()->year, true);
    }

    public function descargarPaqueteYArchivarEnServidor()
    {
        if ($this->archivoCorte === '') {
            session()->flash('status', 'Seleccioná el mes y año a partir del cual se archiva (corte).');

            return null;
        }

        try {
            $cutoff = Carbon::createFromFormat('Y-m', $this->archivoCorte)->startOfMonth();
        } catch (\Throwable) {
            session()->flash('status', 'El mes de corte no es válido.');

            return null;
        }

        $gastos = Gasto::query()
            ->with('consorcio:id,nombre')
            ->where('archivo_disponible_online', true)
            ->where('fecha_factura', '<', $cutoff->toDateString())
            ->where(function (Builder $q): void {
                $q->whereNotNull('factura_archivo')
                    ->orWhereNotNull('comprobante_pago');
            })
            ->orderBy('fecha_factura')
            ->orderBy('id')
            ->get();

        if ($gastos->isEmpty()) {
            session()->flash('status', 'No hay archivos online para archivar con ese corte (por fecha de factura).');

            return null;
        }

        $zipDir = Storage::disk('local')->path('tmp');
        if (! is_dir($zipDir)) {
            mkdir($zipDir, 0755, true);
        }

        $zipPath = $zipDir.'/gastos-archivo-antes-'.$cutoff->format('Y-m').'-'.now()->format('YmdHis').'.zip';
        $zip = new ZipArchive;
        $opened = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        if ($opened !== true) {
            session()->flash('status', 'No se pudo generar el archivo ZIP. Intentá de nuevo.');

            return null;
        }

        foreach ($gastos as $gasto) {
            $consorcioSlug = Str::slug($gasto->consorcio?->nombre ?? 'consorcio');
            $ym = Carbon::parse($gasto->fecha_factura)->format('Y-m');
            $base = $consorcioSlug.'/'.$ym.'/';

            if ($gasto->factura_archivo && Storage::disk('local')->exists($gasto->factura_archivo)) {
                $entry = $base.$gasto->id.'_'.($gasto->factura_nombre_sistema ?: basename($gasto->factura_archivo));
                $zip->addFile(Storage::disk('local')->path($gasto->factura_archivo), $entry);
            }

            if ($gasto->comprobante_pago && Storage::disk('local')->exists($gasto->comprobante_pago)) {
                $entry = $base.$gasto->id.'_comprobante_'.basename($gasto->comprobante_pago);
                $zip->addFile(Storage::disk('local')->path($gasto->comprobante_pago), $entry);
            }
        }

        if ($zip->close() !== true) {
            if (is_file($zipPath)) {
                @unlink($zipPath);
            }
            session()->flash('status', 'Error al finalizar el ZIP. No se archivó nada en el servidor.');

            return null;
        }

        $service = app(GastoService::class);
        $ids = $gastos->pluck('id')->all();
        DB::transaction(function () use ($ids, $service): void {
            foreach ($ids as $id) {
                $service->marcarArchivoLocal(Gasto::query()->findOrFail($id));
            }
        });

        $count = count($ids);

        session()->put('gastos_archive_notice_ack_'.now()->year, true);
        session()->flash('status', 'Se archivaron '.$count.' gasto(s) en el servidor. Guardá el ZIP descargado como respaldo.');

        return response()->download($zipPath, basename($zipPath))->deleteFileAfterSend(true);
    }

    public function descargarTodosProximosVencer()
    {
        $gastos = $this->gastosUrgentesRetencion;
        if ($gastos->isEmpty()) {
            session()->flash('status', 'No hay archivos próximos a vencer para descargar.');

            return null;
        }

        $zipDir = Storage::disk('local')->path('tmp');
        if (! is_dir($zipDir)) {
            mkdir($zipDir, 0755, true);
        }

        $zipPath = $zipDir.'/gastos-proximos-vencer-'.now()->format('YmdHis').'.zip';
        $zip = new ZipArchive;
        $opened = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        if ($opened !== true) {
            abort(500, 'No se pudo generar el archivo ZIP.');
        }

        foreach ($gastos as $gasto) {
            if ($gasto->factura_archivo && Storage::disk('local')->exists($gasto->factura_archivo)) {
                $sourcePath = Storage::disk('local')->path($gasto->factura_archivo);
                $targetName = $gasto->factura_nombre_sistema ?: basename($gasto->factura_archivo);
                $zip->addFile($sourcePath, $targetName);
            }

            if ($gasto->comprobante_pago && Storage::disk('local')->exists($gasto->comprobante_pago)) {
                $sourcePath = Storage::disk('local')->path($gasto->comprobante_pago);
                $targetName = 'comprobante_'.($gasto->factura_nombre_sistema ?: basename($gasto->comprobante_pago));
                $zip->addFile($sourcePath, $targetName);
            }
        }

        $zip->close();

        return response()->download($zipPath, basename($zipPath))->deleteFileAfterSend(true);
    }

    /**
     * Últimos 30 días antes del cumpleaños de 1 año desde fecha_factura (archivos aún online).
     */
    public function getGastosUrgentesRetencionProperty()
    {
        return GastoOnlineRetention::urgentWarningGastos();
    }

    public function render()
    {
        $gastos = Gasto::query()
            ->with(['consorcio:id,nombre', 'proveedor:id,nombre', 'conceptosPresupuesto:id,nombre'])
            ->when($this->consorcioFilter !== '', fn (Builder $q) => $q->where('consorcio_id', (int) $this->consorcioFilter))
            ->when($this->estadoFilter !== '', fn (Builder $q) => $q->where('estado', $this->estadoFilter))
            ->when($this->periodoFilter !== '', function (Builder $q): void {
                $periodo = Carbon::createFromFormat('Y-m', $this->periodoFilter)->startOfMonth()->toDateString();
                $q->whereDate('periodo', $periodo);
            })
            ->when($this->archivoFilter !== '', function (Builder $q): void {
                if ($this->archivoFilter === 'online') {
                    $q->where('archivo_disponible_online', true)
                        ->where(function (Builder $inner): void {
                            $inner->whereNotNull('factura_archivo')
                                ->orWhereNotNull('comprobante_pago');
                        });
                }

                if ($this->archivoFilter === 'archivado') {
                    $q->where('archivo_disponible_online', false);
                }

                if ($this->archivoFilter === 'sin_archivo') {
                    $q->whereNull('factura_archivo')
                        ->whereNull('comprobante_pago');
                }
            })
            ->when($this->search !== '', function (Builder $q): void {
                $term = '%'.$this->search.'%';
                $q->where(function (Builder $nested) use ($term): void {
                    $nested->where('descripcion', 'like', $term)
                        ->orWhere('nro_orden', 'like', $term)
                        ->orWhereHas('consorcio', fn (Builder $c) => $c->where('nombre', 'like', $term))
                        ->orWhereHas('proveedor', fn (Builder $p) => $p->where('nombre', 'like', $term));
                });
            })
            ->orderByDesc('fecha_factura')
            ->orderByDesc('id')
            ->paginate(10);

        $this->ajustesMesSiguiente = $this->resolveAjustesMesSiguiente($gastos);

        return view('livewire.gastos.gasto-index', [
            'gastos' => $gastos,
            'consorcios' => Consorcio::query()->orderBy('nombre')->get(['id', 'nombre']),
            'gastosUrgentesRetencion' => $this->gastosUrgentesRetencion,
            'adjuntosBloqueadosPorRetencion' => GastoOnlineRetention::anyPastDeadlineBlocking(),
            'mostrarAvisoAnualArchivo' => ! session()->has('gastos_archive_notice_ack_'.now()->year),
        ])->layout('layouts.app', ['active' => 'gastos']);
    }

    /**
     * @return array<int, array{has: bool, amount: float}>
     */
    private function resolveAjustesMesSiguiente(LengthAwarePaginator $gastos): array
    {
        /** @var EloquentCollection<int, Gasto> $rows */
        $rows = $gastos->getCollection();
        if ($rows->isEmpty()) {
            return [];
        }

        $result = [];
        foreach ($rows as $gasto) {
            $periodoSiguiente = Carbon::parse($gasto->periodo)->startOfMonth()->addMonth()->toDateString();

            // El ajuste puede estar en el presupuesto del mes calendario siguiente (flujo "próximo mes")
            // o en el último borrador (flujo "último pendiente"), que no coincide siempre con mes+1.
            $presupuestoIds = collect();
            $idMesSiguiente = Presupuesto::query()
                ->where('consorcio_id', $gasto->consorcio_id)
                ->whereDate('periodo', $periodoSiguiente)
                ->value('id');
            if ($idMesSiguiente) {
                $presupuestoIds->push($idMesSiguiente);
            }
            $idUltimoBorrador = Presupuesto::query()
                ->where('consorcio_id', $gasto->consorcio_id)
                ->where('estado', EstadoPresupuesto::Borrador->value)
                ->orderByDesc('periodo')
                ->value('id');
            if ($idUltimoBorrador) {
                $presupuestoIds->push($idUltimoBorrador);
            }
            $presupuestoIds = $presupuestoIds->unique()->filter()->values();

            if ($presupuestoIds->isEmpty()) {
                $result[$gasto->id] = ['has' => false, 'amount' => 0.0];
                continue;
            }

            $nombresAjuste = $gasto->conceptosPresupuesto
                ->pluck('nombre')
                ->map(fn (string $nombre): string => 'Ajuste '.$nombre)
                ->unique()
                ->values();

            if ($nombresAjuste->isEmpty()) {
                $result[$gasto->id] = ['has' => false, 'amount' => 0.0];
                continue;
            }

            $montoAjuste = (float) ConceptoPresupuesto::query()
                ->whereIn('presupuesto_id', $presupuestoIds->all())
                ->whereIn('nombre', $nombresAjuste->all())
                ->where('descripcion', GastoService::AJUSTE_DESCRIPCION_AUTO)
                ->sum('monto_total');

            $result[$gasto->id] = [
                'has' => round($montoAjuste, 2) !== 0.0,
                'amount' => round($montoAjuste, 2),
            ];
        }

        return $result;
    }
}
