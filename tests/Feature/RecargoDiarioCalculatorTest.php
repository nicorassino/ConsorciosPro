<?php

namespace Tests\Feature;

use App\Services\RecargoDiarioCalculator;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class RecargoDiarioCalculatorTest extends TestCase
{
    public function test_calculates_amount_for_second_due_date_with_daily_rate(): void
    {
        $calculator = new RecargoDiarioCalculator();

        $amount = $calculator->calculateSegundoVencimientoAmount(
            capital: 100000,
            recargoMensualPercent: 12,
            primerVencimiento: CarbonImmutable::parse('2026-05-10'),
            segundoVencimiento: CarbonImmutable::parse('2026-05-20'),
        );

        $this->assertSame(104000.0, $amount);
    }
}
