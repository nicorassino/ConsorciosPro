<?php

namespace App\Services;

use App\Models\Cobranza;
use App\Models\Consorcio;
use App\Models\LiquidacionDetalle;
use App\Models\PortalUser;
use App\Models\Unidad;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class SiroApiService
{
    public function __construct(
        private readonly RecargoDiarioCalculator $recargoDiarioCalculator,
    ) {
    }

    public function authenticate(): array
    {
        $baseUrl = rtrim((string) config('services.siro.base_url', ''), '/');
        $apiKey = (string) config('services.siro.api_key', '');

        if ($baseUrl === '' || $apiKey === '') {
            return ['token' => null, 'mode' => 'stub'];
        }

        $response = Http::timeout(15)->post($baseUrl.'/auth/token', [
            'api_key' => $apiKey,
        ]);

        return $response->json();
    }

    public function buildDebtCouponsForUnidad(Unidad $unidad, CarbonImmutable $today): Collection
    {
        return LiquidacionDetalle::query()
            ->with(['liquidacionConcepto.liquidacion.consorcio'])
            ->where('unidad_id', $unidad->id)
            ->where('monto_calculado', '>', 0)
            ->get()
            ->groupBy(fn (LiquidacionDetalle $detalle) => optional($detalle->liquidacionConcepto?->liquidacion?->periodo)?->format('Y-m'))
            ->filter(fn ($group, ?string $periodo) => ! empty($periodo))
            ->map(function (Collection $detalles, string $periodo) use ($unidad, $today): array {
                $sample = $detalles->first();
                $liquidacion = $sample->liquidacionConcepto->liquidacion;
                $capital = (float) $detalles->sum('monto_calculado');
                $primerVto = CarbonImmutable::parse($liquidacion->fecha_primer_vto);
                $segundoVto = $liquidacion->fecha_segundo_vto
                    ? CarbonImmutable::parse($liquidacion->fecha_segundo_vto)
                    : $primerVto;
                $recargoMensual = (float) ($liquidacion->presupuesto?->recargo_segundo_vto_real ?? $liquidacion->consorcio->recargo_segundo_vto ?? 0);
                $montoSegundoVto = $this->recargoDiarioCalculator->calculateSegundoVencimientoAmount(
                    $capital,
                    $recargoMensual,
                    $primerVto,
                    $segundoVto
                );
                $montoHoy = $this->recargoDiarioCalculator->calculateAmountForDate(
                    $capital,
                    $recargoMensual,
                    $primerVto,
                    $today
                );

                $coupon = [
                    'periodo' => $periodo,
                    'unidad' => $unidad->numero,
                    'nro_cupon_siro' => $unidad->nro_cupon_siro,
                    'codigo_pago_electronico' => $unidad->codigo_pago_electronico,
                    'capital' => round($capital, 2),
                    'monto_primer_vto' => round($capital, 2),
                    'monto_segundo_vto' => $montoSegundoVto,
                    'monto_actual' => $montoHoy,
                    'fecha_primer_vto' => $primerVto->toDateString(),
                    'fecha_segundo_vto' => $segundoVto->toDateString(),
                ];

                $consorcio = $liquidacion->consorcio;
                $coupon['pago_url'] = $this->buildCouponPaymentUrl($unidad, $coupon, $consorcio);

                return $coupon;
            })
            ->values();
    }

    public function reportDebtToSiro(PortalUser $portalUser, CarbonImmutable $today): array
    {
        $coupons = $this->buildDebtCouponsForUnidad($portalUser->unidad, $today);

        return [
            'tenant_email' => $portalUser->email,
            'coupons' => $coupons->all(),
        ];
    }

    public function registerAccreditation(array $payload): Cobranza
    {
        $capital = (float) ($payload['monto_capital'] ?? 0);
        $interes = (float) ($payload['monto_interes'] ?? 0);

        return Cobranza::create([
            'consorcio_id' => (int) $payload['consorcio_id'],
            'unidad_id' => (int) $payload['unidad_id'],
            'fecha_pago' => $payload['fecha_pago'],
            'monto_capital' => $capital,
            'monto_interes' => $interes,
            'total_pagado' => round($capital + $interes, 2),
            'medio_pago' => $payload['medio_pago'] ?? 'siro',
            'comprobante_path' => $payload['comprobante_path'] ?? null,
            'liquidacion_detalle_id' => $payload['liquidacion_detalle_id'] ?? null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $coupon
     */
    public function buildCouponPaymentUrl(Unidad $unidad, array $coupon, ?Consorcio $consorcio = null): ?string
    {
        $consorcio ??= $unidad->consorcio;

        $template = trim((string) config('services.siro.payment_url_template', ''));
        if ($template !== '') {
            return $this->interpolatePaymentTemplate($unidad, $coupon, $consorcio, $template);
        }

        $base = trim((string) config('services.siro.payment_base_url', ''));
        if ($base === '') {
            return null;
        }

        $query = array_filter([
            'codigo_pago_electronico' => $coupon['codigo_pago_electronico'] ?? null,
            'nro_cupon_siro' => $coupon['nro_cupon_siro'] ?? null,
            'importe' => isset($coupon['monto_actual']) ? number_format((float) $coupon['monto_actual'], 2, '.', '') : null,
            'importe_primer_vto' => isset($coupon['monto_primer_vto']) ? number_format((float) $coupon['monto_primer_vto'], 2, '.', '') : null,
            'periodo' => $coupon['periodo'] ?? null,
            'cuit' => $consorcio?->cuit,
            'unidad' => $coupon['unidad'] ?? $unidad->numero,
            'consorcio_id' => $consorcio?->id,
        ], fn ($v) => $v !== null && $v !== '');

        $separator = str_contains($base, '?') ? '&' : '?';

        return $base.$separator.http_build_query($query);
    }

    /**
     * @param  array<string, mixed>  $coupon
     */
    private function interpolatePaymentTemplate(
        Unidad $unidad,
        array $coupon,
        ?Consorcio $consorcio,
        string $template,
    ): string {
        $importe = isset($coupon['monto_actual']) ? number_format((float) $coupon['monto_actual'], 2, '.', '') : '';
        $importePrimer = isset($coupon['monto_primer_vto']) ? number_format((float) $coupon['monto_primer_vto'], 2, '.', '') : '';

        $map = [
            '{codigo_pago_electronico}' => rawurlencode((string) ($coupon['codigo_pago_electronico'] ?? '')),
            '{nro_cupon_siro}' => rawurlencode((string) ($coupon['nro_cupon_siro'] ?? '')),
            '{importe}' => rawurlencode($importe),
            '{importe_primer_vto}' => rawurlencode($importePrimer),
            '{periodo}' => rawurlencode((string) ($coupon['periodo'] ?? '')),
            '{unidad}' => rawurlencode((string) ($coupon['unidad'] ?? $unidad->numero)),
            '{cuit}' => rawurlencode((string) ($consorcio?->cuit ?? '')),
            '{consorcio_id}' => rawurlencode((string) ($consorcio?->id ?? '')),
        ];

        return str_replace(array_keys($map), array_values($map), $template);
    }
}
