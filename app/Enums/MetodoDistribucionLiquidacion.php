<?php

namespace App\Enums;

enum MetodoDistribucionLiquidacion: string
{
    case Coeficiente = 'coeficiente';
    case PartesIguales = 'partes_iguales';
    case Manual = 'manual';

    public function label(): string
    {
        return match ($this) {
            self::Coeficiente => 'Coeficiente',
            self::PartesIguales => 'Partes iguales',
            self::Manual => 'Manual',
        };
    }
}
