<?php

namespace App\Services;

use Carbon\CarbonImmutable;

class RecargoDiarioCalculator
{
    public function calculateSegundoVencimientoAmount(
        float $capital,
        float $recargoMensualPercent,
        CarbonImmutable $primerVencimiento,
        CarbonImmutable $segundoVencimiento,
    ): float {
        $dias = max(0, $primerVencimiento->diffInDays($segundoVencimiento));
        $interes = $this->calculateInteres($capital, $recargoMensualPercent, $dias);

        return round($capital + $interes, 2);
    }

    public function calculateAmountForDate(
        float $capital,
        float $recargoMensualPercent,
        CarbonImmutable $primerVencimiento,
        CarbonImmutable $fechaPago,
    ): float {
        $dias = max(0, $primerVencimiento->diffInDays($fechaPago, false));
        if ($dias <= 0) {
            return round($capital, 2);
        }

        $interes = $this->calculateInteres($capital, $recargoMensualPercent, $dias);

        return round($capital + $interes, 2);
    }

    public function calculateInteres(float $capital, float $recargoMensualPercent, int $dias): float
    {
        if ($capital <= 0 || $recargoMensualPercent <= 0 || $dias <= 0) {
            return 0.0;
        }

        $tasaDiaria = ($recargoMensualPercent / 100) / 30;

        return round($capital * $tasaDiaria * $dias, 2);
    }
}
