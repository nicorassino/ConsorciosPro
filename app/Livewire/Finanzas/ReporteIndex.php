<?php

namespace App\Livewire\Finanzas;

use App\Models\Consorcio;
use App\Services\FinanceReportService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Livewire\Component;

class ReporteIndex extends Component
{
    public string $consorcioFilter = '';

    public string $periodFilter = '';

    public string $tab = 'conciliacion';

    public function mount(): void
    {
        $this->periodFilter = now()->startOfMonth()->format('Y-m');
    }

    public function updatedConsorcioFilter(): void
    {
        // trigger rerender only
    }

    public function updatedPeriodFilter(): void
    {
        // trigger rerender only
    }

    public function setTab(string $tab): void
    {
        $allowed = ['conciliacion', 'economico', 'deuda', 'estadisticas'];
        if (in_array($tab, $allowed, true)) {
            $this->tab = $tab;
        }
    }

    public function exportCsv(FinanceReportService $reports)
    {
        [$consorcio, $periodStart] = $this->currentContext();
        $data = $reports->build($consorcio->id, $periodStart);
        $filename = 'reporte_'.$this->tab.'_'.$consorcio->id.'_'.$periodStart->format('Y_m').'.csv';

        return response()->streamDownload(function () use ($data): void {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }
            fwrite($out, "\xEF\xBB\xBF");

            if ($this->tab === 'conciliacion') {
                fputcsv($out, ['Concepto', 'Monto']);
                foreach ([
                    'Saldo inicial' => $data['conciliacion']['saldo_inicial'],
                    'Ingresos' => $data['conciliacion']['ingresos'],
                    'Egresos' => $data['conciliacion']['egresos'],
                    'Saldo teórico' => $data['conciliacion']['saldo_teorico'],
                    'Saldo disponible' => $data['conciliacion']['saldo_disponible'],
                    'Obligaciones pendientes' => $data['conciliacion']['obligaciones_pendientes'],
                    'Holgura' => $data['conciliacion']['holgura'],
                ] as $label => $value) {
                    fputcsv($out, [$label, number_format((float) $value, 2, '.', '')]);
                }
            } elseif ($this->tab === 'economico') {
                fputcsv($out, ['Rubro', 'Concepto', 'Devengado', 'Pagado mes']);
                foreach ($data['informe']['rows'] as $row) {
                    fputcsv($out, [
                        $row['rubro'],
                        $row['concepto'],
                        number_format((float) $row['devengado'], 2, '.', ''),
                        number_format((float) $row['percibido'], 2, '.', ''),
                    ]);
                }
            } elseif ($this->tab === 'deuda') {
                fputcsv($out, ['Tipo', 'Nombre', 'Liquidado/Compromisos', 'Cobrado', 'Saldo/Pendiente']);
                foreach ($data['deuda']['deudores'] as $deudor) {
                    fputcsv($out, [
                        'Deudor',
                        'Unidad '.$deudor['unidad'],
                        number_format((float) $deudor['liquidado'], 2, '.', ''),
                        number_format((float) $deudor['cobrado'], 2, '.', ''),
                        number_format((float) $deudor['saldo'], 2, '.', ''),
                    ]);
                }
                foreach ($data['deuda']['proveedores'] as $prov) {
                    fputcsv($out, [
                        'Proveedor',
                        $prov['proveedor'],
                        (string) $prov['compromisos'],
                        '',
                        number_format((float) $prov['monto_pendiente'], 2, '.', ''),
                    ]);
                }
            } else {
                fputcsv($out, ['Indicador', 'Valor']);
                foreach ([
                    'Capital cobrado' => $data['stats']['capital_cobrado'],
                    'Interés cobrado' => $data['stats']['interes_cobrado'],
                    'Total cobrado' => $data['stats']['total_cobrado'],
                    'Pagos registrados' => $data['stats']['pagos_registrados'],
                    'Cobrabilidad (%)' => $data['stats']['cobrabilidad'],
                ] as $name => $value) {
                    fputcsv($out, [$name, is_numeric($value) ? number_format((float) $value, 2, '.', '') : $value]);
                }
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportResumenPdf(FinanceReportService $reports)
    {
        [$consorcio, $periodStart] = $this->currentContext();
        $data = $reports->build($consorcio->id, $periodStart);
        $html = view('pdf.finanzas-resumen', ['data' => $data])->render();
        $name = 'reporte_finanzas_'.$consorcio->id.'_'.$periodStart->format('Y_m').'.pdf';
        $domPdfFacade = 'Barryvdh\\DomPDF\\Facade\\Pdf';

        if (class_exists($domPdfFacade)) {
            $binary = $domPdfFacade::loadHTML($html)->output();

            return response($binary, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$name.'"',
            ]);
        }

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.Str::replace('.pdf', '.html', $name).'"',
        ]);
    }

    public function render(FinanceReportService $reports)
    {
        $consorcios = Consorcio::query()->orderBy('nombre')->get(['id', 'nombre']);
        if ($this->consorcioFilter === '' && $consorcios->isNotEmpty()) {
            $this->consorcioFilter = (string) $consorcios->first()->id;
        }

        $periodStart = CarbonImmutable::createFromFormat('Y-m', $this->periodFilter)->startOfMonth();
        $data = null;
        if ($this->consorcioFilter !== '') {
            $data = $reports->build((int) $this->consorcioFilter, $periodStart);
        }

        return view('livewire.finanzas.reporte-index', [
            'consorcios' => $consorcios,
            'data' => $data,
        ])->layout('layouts.app', ['active' => 'reportes']);
    }

    /**
     * @return array{0:Consorcio,1:CarbonImmutable}
     */
    private function currentContext(): array
    {
        $consorcio = Consorcio::query()->findOrFail((int) $this->consorcioFilter);
        $periodStart = CarbonImmutable::createFromFormat('Y-m', $this->periodFilter)->startOfMonth();

        return [$consorcio, $periodStart];
    }
}
