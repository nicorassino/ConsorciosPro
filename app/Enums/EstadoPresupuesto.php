<?php

namespace App\Enums;

enum EstadoPresupuesto: string
{
    case Borrador = 'borrador';
    case Finalizado = 'finalizado';
    case Liquidado = 'liquidado';

    public function label(): string
    {
        return match ($this) {
            self::Borrador => 'Borrador',
            self::Finalizado => 'Finalizado',
            self::Liquidado => 'Liquidado',
        };
    }
}
