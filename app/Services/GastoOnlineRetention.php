<?php

namespace App\Services;

use App\Models\Gasto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Retención online de adjuntos según fecha_factura: 1 año desde la factura.
 * Ventana de aviso urgente: últimos 30 días antes de cumplir el año.
 */
final class GastoOnlineRetention
{
    public const WARNING_DAYS_BEFORE_DEADLINE = 30;

    public static function baseOnlineWithFiles(): Builder
    {
        return Gasto::query()
            ->where('archivo_disponible_online', true)
            ->where(function (Builder $q): void {
                $q->whereNotNull('factura_archivo')
                    ->orWhereNotNull('comprobante_pago');
            });
    }

    /**
     * Hay archivos online cuya fecha límite de retención (fecha_factura + 1 año) ya pasó.
     */
    public static function anyPastDeadlineBlocking(): bool
    {
        return self::applyPastDeadline(self::baseOnlineWithFiles())->exists();
    }

    /**
     * Gastos en ventana roja: falta menos de 30 días para cumplir el año desde fecha_factura (y aún no venció).
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Gasto>
     */
    public static function urgentWarningGastos()
    {
        return self::applyUrgentWarningWindow(self::baseOnlineWithFiles())
            ->orderBy('fecha_factura')
            ->get();
    }

    public static function applyUrgentWarningWindow(Builder $q): Builder
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            $days = self::WARNING_DAYS_BEFORE_DEADLINE;

            return $q
                ->whereRaw("date(fecha_factura, '+1 year') > date('now')")
                ->whereRaw("date(fecha_factura, '+1 year') <= date('now', '+{$days} days')");
        }

        return $q
            ->whereRaw('DATE_ADD(fecha_factura, INTERVAL 1 YEAR) > CURDATE()')
            ->whereRaw('DATE_ADD(fecha_factura, INTERVAL 1 YEAR) <= DATE_ADD(CURDATE(), INTERVAL ? DAY)', [self::WARNING_DAYS_BEFORE_DEADLINE]);
    }

    public static function applyPastDeadline(Builder $q): Builder
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            return $q->whereRaw("date(fecha_factura, '+1 year') <= date('now')");
        }

        return $q->whereRaw('DATE_ADD(fecha_factura, INTERVAL 1 YEAR) <= CURDATE()');
    }
}
